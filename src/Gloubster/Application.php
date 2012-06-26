<?php

namespace Gloubster;

use Gloubster\Documents\JobSet;
use Silex\Application as SilexApp;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Form\FormError;

class Application implements ControllerProviderInterface
{

    public function connect(SilexApp $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function() use ($app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\JobSet');

                $jobsets = $repository->findAll();

                return $app['twig']->render('index.html.twig', array('jobsets' => $jobsets));
            })->Bind('homepage');

        $controllers->match('/jobsets/create', function() use ($app) {

                $form = $app['form.factory']->createBuilder('form')
                        ->add('file', 'text', array(
                            'label'       => 'File (HTTP)',
                            'constraints' => array(
                                new NotBlank(),
                                new Url(),
                            ),
                        ))->getForm();

                if ('POST' === $app['request']->getMethod()) {
                    $form->bindRequest($app['request']);

                    if ($form->isValid()) {
                        $jobset = new JobSet();
                        $jobset->setFile($form->get('file')->getData());

                        $app['dm']->persist($jobset);
                        $app['dm']->flush();

                        $app['session']->setFlash('success', 'Jobset created');

                        return $app->redirect($app['url_generator']->generate('homepage'));
                    }
                }

                return $app['twig']->render('jobset-create.html.twig', array('form' => $form->createView()));
            })->Bind('jobset_create');

        $controllers->get('/jobset/{jobset_id}', function($jobset_id, SilexApp $app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\JobSet');

                $jobset = $repository->find($jobset_id);

                if ($jobset === null) {
                    throw new NotFoundHttpException('Jobset not found');
                }

                return $app['twig']->render('jobset.html.twig', array('jobset' => $jobset));
            })->Bind('jobset')->assert('jobset_id', '[a-fA-F0-9]{24}');

        $controllers->match('/jobset/{jobset_id}/edit', function($jobset_id, SilexApp $app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\JobSet');

                $jobset = $repository->find($jobset_id);

                if ($jobset === null) {
                    throw new NotFoundHttpException('Jobset not found');
                }

                $form = $app['form.factory']->createBuilder('form')
                        ->add('file', 'text', array(
                            'label'       => 'File (HTTP)',
                            'constraints' => array(new NotBlank(), new Url())
                        ))->getForm();

                if ('POST' === $app['request']->getMethod()) {
                    $form->bindRequest($app['request']);

                    if ($form->isValid()) {
                        $jobset->setFile($form->get('file')->getData());

                        $app['dm']->persist($jobset);
                        $app['dm']->flush();

                        $app['session']->setFlash('notice', 'Jobset updated');

                        return $app->redirect(
                                $app['url_generator']->generate(
                                    'jobset', array(
                                    'jobset_id' => $jobset->getId()
                                    )
                                )
                        );
                    }
                }

                $form->get('file')->setData($jobset->getFile());

                return $app['twig']->render(
                        'jobset-edit.html.twig', array(
                        'jobset' => $jobset,
                        'form'   => $form->createView(),
                        )
                );
            })->Bind('jobset_edit')->assert('jobset_id', '[a-fA-F0-9]{24}');


        $controllers->get('/jobset/{jobset_id}/delete', function($jobset_id, SilexApp $app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\JobSet');

                $jobset = $repository->find($jobset_id);

                if ($jobset === null) {
                    throw new NotFoundHttpException('Jobset not found');
                }

                return $app['twig']->render('jobset-delete-confirm.html.twig', array('jobset' => $jobset));
            })->Bind('jobset_delete_confirm')->assert('jobset_id', '[a-fA-F0-9]{24}');

        $controllers->post('/jobset/{jobset_id}/delete', function($jobset_id, SilexApp $app) {

                $repository = $app['dm']->getRepository('Gloubster\\Documents\\JobSet');

                $jobset = $repository->find($jobset_id);

                if ($jobset === null) {
                    throw new NotFoundHttpException('Jobset not found');
                }

                $app['dm']->remove($jobset);
                $app['dm']->flush();

                $app['session']->setFlash('success', 'The jobset has been removed');

                return new RedirectResponse($app['url_generator']->generate('homepage'));
            })->Bind('jobset_delete')->assert('jobset_id', '[a-fA-F0-9]{24}');

        $controllers->before(function() use ($app) {
                $app['session']->start();
            });

        return $controllers;
    }
}
