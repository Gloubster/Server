<?php

namespace Gloubster;

use Silex\Application as SilexApp;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Application implements ControllerProviderInterface
{

    public function connect(SilexApp $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function() use ($app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\Specification');

                $specifications = $repository->findAll();

                return $app['twig']->render('index.html.twig', array('specifications' => $specifications));
            })->bind('homepage');

        $controllers->get('/jobset/{jobset_id}', function($jobset_id, SilexApp $app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\JobSet');

                if (null === $jobset = $repository->find($jobset_id)) {
                    throw new NotFoundHttpException('Jobset not found');
                }

                return $app['twig']->render('jobset.html.twig', array('jobset' => $jobset));
            })->bind('jobset')->assert('jobset_id', '[a-fA-F0-9]{24}');

        $controllers->get('/jobset/{jobset_id}/delete', function($jobset_id, SilexApp $app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\JobSet');

                if (null === $jobset = $repository->find($jobset_id)) {
                    throw new NotFoundHttpException('Jobset not found');
                }

                return $app['twig']->render('jobset-delete-confirm.html.twig', array('jobset' => $jobset));
            })->bind('jobset_delete_confirm')->assert('jobset_id', '[a-fA-F0-9]{24}');

        $controllers->post('/jobset/{jobset_id}/delete', function($jobset_id, SilexApp $app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\JobSet');

                if (null === $jobset = $repository->find($jobset_id)) {
                    throw new NotFoundHttpException('Jobset not found');
                }

                $app['dm']->remove($jobset);
                $app['dm']->flush();

                $app['session']->setFlash('success', 'The jobset has been removed');

                return new RedirectResponse($app['url_generator']->generate('homepage'));
            })->bind('jobset_delete')->assert('jobset_id', '[a-fA-F0-9]{24}');


        $controllers->get('/graph', function(SilexApp $app) {

                $specifications = $app['dm']->createQueryBuilder('Gloubster\\Documents\\Specification')
                    ->field('done', true)
                    ->sort('submittedOn', 'ASC')
                    ->getQuery()
                    ->execute();

                $workers = array();

                $n = 0;
                $stop = 0;
                $start = microtime(true);
                $durations = 0;

                foreach ($specifications as $spec) {

                    $spec->setTimers(unserialize($spec->getTimers()));

                    if ($spec->getStart()) {
                        $start = min($spec->getStart(), $start);
                    }
                    if ($spec->getStop()) {
                        $stop = max($stop, $spec->getStop());
                    }
                    $workers[$spec->getWorkerName()][] = $spec;
                    if ($spec->getDone()) {
                        $n ++;
                        $durations += ($spec->getStop() - $spec->getStart());
                    }

                }

                $avg = $durations/$n;
                ksort($workers);

                return $app['twig']->render('graph.html.twig', array('avg'=>$avg,  'workers' => $workers, 'start'   => $start, 'stop'    => $stop, 'total'   => $n));
            })->bind('graph');

        $controllers->before(function() use ($app) {
                $app['session']->start();
            });

        return $controllers;
    }
}
