<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Language controller.
 *
 * @Route("/language")
 */
class LanguageController extends Controller
{
    /**
     * @Route("/switch", name="cocorico_language_switch")
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function switchAction(Request $request)
    {
        $routeName = $request->attributes->get('_route');
        $routeParams = $request->attributes->get('_route_params');
        $queryString = $request->query->all();
        if ($routeName == 'cocorico_language_switch') {
            $path = $request->get('path');
            $uri = "";
            $queryStringParams = [];
            if ($pos = strpos($path, '?')) {
                $uri = substr($path, $pos + 1);
                $path = substr($path, 0, $pos);
                parse_str($uri, $queryStringParams);
            }

            $matchinfo = $this->get('router')->match($path);

            $routeName = $matchinfo['_route'];
            unset($matchinfo['_route']);
            unset($matchinfo['_controller']);
            $queryString = $queryStringParams;
            $routeParams = $matchinfo;
        }


        $languagesLinks = $this->container->get('cocorico.language.manager')
            ->getLanguageLinks($routeName, $routeParams, $queryString);

        return $this->render(
            '@CocoricoCore/Frontend/Common/language_switcher.html.twig',
            array(
                'languages_links' => $languagesLinks,
            )
        );
    }

    /**
     * translate data action
     *
     * @Route("/translate", name="cocorico_language_translate")
     * @Method({"POST"})
     *
     * @param Request $request
     *
     * @return Response
     */
    public function translateDataAction(Request $request)
    {
        $response = array('textData' => '');
        if ($request->isXmlHttpRequest()) {
            $from = $request->request->get('from');
            $to = $request->request->get('to');
            $text = $request->request->get('textData');

            $translateManager = $this->container->get('cocorico.translator.manager');
            $response['textData'] = $translateManager->getTranslation($from, $to, $text);
        }

        return new Response(json_encode($response), 200);
    }
}
