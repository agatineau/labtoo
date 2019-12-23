<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\DisputeBundle\Mailer;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

class TwigSwiftMailer
{
    const TRANS_DOMAIN = 'cocorico_mail';

    protected $mailer;
    protected $router;
    protected $twig;
    protected $requestStack;
    protected $translator;
    protected $timeUnit;
    protected $timeUnitIsDay;
    /** @var  array locales */
    protected $locales;
    protected $templates;
    protected $fromEmail;
    protected $adminEmail;

    /**
     * @param \Swift_Mailer         $mailer
     * @param UrlGeneratorInterface $router
     * @param \Twig_Environment     $twig
     * @param RequestStack          $requestStack
     * @param Translator            $translator
     * @param array                 $parameters
     * @param array                 $templates
     */
    public function __construct(
        \Swift_Mailer $mailer,
        UrlGeneratorInterface $router,
        \Twig_Environment $twig,
        RequestStack $requestStack,
        Translator $translator,
        array $parameters,
        array $templates
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->translator = $translator;

        /** parameters */
        $parameters = $parameters['parameters'];

        $this->fromEmail = $parameters['cocorico_from_email'];
        $this->adminEmail = $parameters['cocorico_contact_email'];

        $this->timeUnit = $parameters['cocorico_time_unit'];
        $this->timeUnitIsDay = ($this->timeUnit % 1440 == 0) ? true : false;

        $this->locales = $parameters['cocorico_locales'];
        $this->locale = $parameters['cocorico_locale'];
        if ($requestStack->getCurrentRequest()) {
            $this->locale = $requestStack->getCurrentRequest()->getLocale();
        }

        $this->templates = $templates['templates'];
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingDeferredByAskerMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);

        $template = $this->templates['booking_deferred_by_asker_asker'];

        $bookingUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_asker',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $booking->getUser(),
            'offerer' => $booking->getListing()->getUser(),
            'listing' => $booking->getListing(),
            'booking' => $booking,
            'booking_url' => $bookingUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingDeferredByAskerMessageToOfferer(Booking $booking)
    {
        $user = $booking->getListing()->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);

        $template = $this->templates['booking_deferred_by_asker_offerer'];

        $bookingUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_offerer',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $booking->getUser(),
            'offerer' => $booking->getListing()->getUser(),
            'listing' => $booking->getListing(),
            'booking' => $booking,
            'booking_url' => $bookingUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingDisputedByAskerMessageToAsker(Booking $booking)
    {
        $user = $booking->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);

        $template = $this->templates['booking_disputed_by_asker_asker'];

        $bookingUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_asker',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $booking->getUser(),
            'offerer' => $booking->getListing()->getUser(),
            'listing' => $booking->getListing(),
            'booking' => $booking,
            'booking_url' => $bookingUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingDisputedByAskerMessageToOfferer(Booking $booking)
    {
        $user = $booking->getListing()->getUser();
        $userLocale = $user->guessPreferredLanguage($this->locales, $this->locale);

        $template = $this->templates['booking_disputed_by_asker_offerer'];

        $bookingUrl = $this->router->generate(
            'cocorico_dashboard_booking_show_offerer',
            array(
                'id' => $booking->getId(),
                '_locale' => $userLocale
            ),
            true
        );

        $context = array(
            'user' => $user,
            'asker' => $booking->getUser(),
            'offerer' => $booking->getListing()->getUser(),
            'listing' => $booking->getListing(),
            'booking' => $booking,
            'booking_url' => $bookingUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $user->getEmail());
    }

    /**
     * @param Booking $booking
     */
    public function sendBookingDisputedByAskerMessageToAdmin(Booking $booking)
    {
        $template = $this->templates['booking_disputed_by_asker_admin'];

        $bookingUrl = $this->router->generate(
            'admin_cocorico_core_booking_edit',
            array(
                'id' => $booking->getId()
            ),
            true
        );

        $context = array(
            'asker' => $booking->getUser(),
            'offerer' => $booking->getListing()->getUser(),
            'listing' => $booking->getListing(),
            'booking' => $booking,
            'booking_url' => $bookingUrl
        );

        $this->sendMessage($template, $context, $this->fromEmail, $this->adminEmail);
    }


    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail)
    {
        $context['trans_domain'] = self::TRANS_DOMAIN;

        $context['user_locale'] = $this->locale;
        $context['locale'] = $this->locale;
        $context['app']['request']['locale'] = $this->locale;

        if (isset($context['user'])) {//user receiving the email
            /** @var User $user */
            $user = $context['user'];
            $context['user_locale'] = $user->guessPreferredLanguage($this->locales, $this->locale);
            $context['locale'] = $context['user_locale'];
            $context['app']['request']['locale'] = $context['user_locale'];
        }

        if (isset($context['listing'])) {
            /** @var Listing $listing */
            $listing = $context['listing'];
            $translations = $listing->getTranslations();
            if ($translations->count() && isset($translations[$context['user_locale']])) {
                $slug = $translations[$context['user_locale']]->getSlug();
                $title = $translations[$context['user_locale']]->getTitle();
            } else {
                $slug = $listing->getSlug();
                $title = $listing->getTitle();
            }
            $context['listing_public_url'] = $this->router->generate(
                'cocorico_listing_show',
                array(
                    '_locale' => $context['user_locale'],
                    'slug' => $slug
                ),
                true
            );

            $context['listing_title'] = $title;
        }

        if (isset($context['booking'])) {
            $context['booking_time_range_title'] = $context['booking_time_range'] = '';
            if (!$this->timeUnitIsDay) {
                /** @var Booking $booking */
                $booking = $context['booking'];
                $context['booking_time_range_title'] = $this->translator->trans(
                    'booking.time_range.title',
                    array(),
                    'cocorico_mail',
                    $context['user_locale']
                );
                $context['booking_time_range'] .= $booking->getStartTime()->format('H:i') . " - " .
                    $booking->getEndTime()->format('H:i');
            }
        }

        /** @var \Twig_Template $template */
        $template = $this->twig->loadTemplate($templateName);
        $context = $this->twig->mergeGlobals($context);

        $subject = $template->renderBlock('subject', $context);
        $context["message"] = $template->renderBlock('message', $context);

        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

//        echo 'subject:' . $subject . 'endsubject';
//        echo 'htmlBody:' . $htmlBody . 'endhtmlBody';
//        echo 'textBody:' . $textBody . 'endtextBody';

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (!empty($htmlBody)) {
            $message
                ->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->mailer->send($message);
    }

}
