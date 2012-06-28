<?php

namespace Gloubster;

use JsonSchema\Validator;
use Silex\Application as SilexApp;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

                $validator = new Validator();
                $validator->check(json_decode($request->getContent()), json_decode(file_get_contents(__DIR__ . '/../../ressource/json/jobset.json')));

                if (false === $validator->isValid()) {
                    $errors = array();
                    foreach ($validator->getErrors() as $error) {
                        $errors[]  =sprintf("[%s] %s\n", $error['property'], $error['message']);
                    }
                    throw new HttpException(400, implode("\n", $errors));
                }

                $datas = json_decode($request->getContent(), true);

                $jobset = new Documents\JobSet();
                $jobset->setFile($datas['file']);

                if (isset($datas['specifications'])) {
                    foreach ($datas['specifications'] as $spec) {
                        $specification = new Documents\Specification();
                        $specification->setName($spec['name']);

                        if ( ! isset($spec['parameters'])) {
                            continue;
                        }

                        foreach ($spec['parameters'] as $name => $value) {
                            $param = new Documents\Parameter();

                            $param->setName($name);
                            $param->setValue($value);

                            $specification->addParameters($param);
                            $app['dm']->persist($param);
                        }

                        $jobset->addSpecifications($specification);
                        $app['dm']->persist($specification);
                    }
                }

                $app['dm']->persist($jobset);
                $app['dm']->flush();
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