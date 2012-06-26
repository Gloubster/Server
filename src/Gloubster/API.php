<?php

namespace Gloubster;

use Silex\Application as SilexApp;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class API implements ControllerProviderInterface
{

    public function connect(SilexApp $app)
    {
        $controllers = $app['controllers_factory'];

        /**
         * root
         *
         * display infos
         */
        $controllers->get('/', function(SilexApp $app, Request $request) {

            })->bind('api_root');

        /**
         * Creates a jobset
         */
        $controllers->put('/jobsets', function(SilexApp $app, Request $request) {

                filter_var($request, FILTER_VALIDATE_URL);
            })->bind('api_jobset_create');

        /**
         * Get a jobset
         */
        $controllers->get('/jobset/{jobset_id}', function($jobset_id, SilexApp $app, Request $request) {

                $repository = $app['doctrine.odm.mongodb.dm']->getRepository('Gloubster\\Documents\\JobSet');

                $jobset = $repository->find($jobset_id);

                if ($jobset === null) {
                    throw new NotFoundHttpException('Jobset not found');
                }

                $datas = array(
                    'id'   => $jobset->getId(),
                    'file' => $jobset->getFile(),
                );

                return new JsonResponse($datas);
            })->bind('api_jobset_retrieve')->assert('jobset_id', '[a-fA-F0-9]{24}');

        /**
         * Delete a jobset
         */
        $controllers->delete('/jobset/{jobset_id}', function($jobset_id, SilexApp $app, Request $request) {

            })->bind('api_jobset_delete')->assert('jobset_id', '[a-fA-F0-9]{24}');

        return $controllers;
    }
}