<?php

namespace eLife\HypothesisClient\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PingController extends Controller
{
    /**
     * @Route("/ping", name="ping")
     * @Method("GET")
     */
    public function pingAction(Request $request)
    {
        return new Response(
            'pong',
            200,
            [
                'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]
        );
    }
}
