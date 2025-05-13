<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace StockAlert\Controller;

use StockAlert\Event\StockAlertEvent;
use StockAlert\Event\StockAlertEvents;
use StockAlert\Form\StockAlertSubscribe;
use StockAlert\StockAlert;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Controller\Front\BaseFrontController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Translation\Translator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: "/module/stockalert", name: "stockalert_front")]
class StockAlertFrontOfficeController extends BaseFrontController
{
    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param RequestStack $requestStack
     * @return RedirectResponse|Response
     * @throws \JsonException
     */
    #[Route(path: "/subscribe", name: "_subscribe", methods: ["POST"])]
    public function subscribe(
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack
    ): Response|RedirectResponse
    {
        $success = true;
        $request = $requestStack->getCurrentRequest();

        $form = $this->createForm(
            StockAlertSubscribe::getName(),
            FormType::class,
            [],
            ['csrf_protection' => false]
        );

        try {
            $data = $this->validateForm($form)->getData();

            $event = new StockAlertEvent(
                $data['product_sale_elements_id'],
                $data['email'],
                $data['newsletter'] ?? false,
                $request?->getSession()->getLang()->getLocale()
            );

            $eventDispatcher->dispatch(
                $event,
                StockAlertEvents::STOCK_ALERT_SUBSCRIBE
            );

            $message = Translator::getInstance()->trans(
                "C’est noté ! Vous recevrez un e-mail dès que le produit sera de nouveau en stock.",
                [],
                StockAlert::MESSAGE_DOMAIN
            );
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        if (!$request?->isXmlHttpRequest()) {
            $request?->getSession()
                ->getFlashBag()
                ->set('flashMessage', $message);

            $redirectUrl = $data['success_url'] ?? $this->generateUrl('homepage');
            return new RedirectResponse($redirectUrl);
        }

        return $this->jsonResponse(json_encode([
            'success' => $success,
            'message' => $message,
        ], JSON_THROW_ON_ERROR));
    }
}
