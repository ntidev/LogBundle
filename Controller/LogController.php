<?php

namespace NTI\LogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use NTI\LogBundle\Entity\Log;

/**
 * Log controller.
 */
class LogController extends Controller
{

    /**
     * Lists all Log entities.
     *
     * @Route("/", name="nti_log")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->render('LogBundle:Log:index.html.twig');
    }

    /**
     * Finds and displays a Log entity.
     *
     * @Route("/{id}/show", name="log_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $log = $em->getRepository('LogBundle:Log')->find($id);
        if (!$log)
            throw $this->createNotFoundException('Unable to find Log entity.');
        return array(
            'log'      => $log,
        );
    }

    /**
     * Finds and displays a Log entity.
     *
     * @Route("/list", name="log_ajax_list")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {

        $draw = $request->get('draw');
        $recordsTotal = $request->get('recordsTotal');
        $recordsFiltered = $request->get('recordsFiltered');
        $data = $request->get('data');
        $search = $request->get('search');
        $searchParams = $search['value'];
        $length = ($request->get('length') > 0) ? $request->get('length') : 20;
        $start = ($request->get('start') > 0) ? $request->get('start') : 0;

        $em = $this->getDoctrine()->getManager();

        $logs = $em->getRepository('LogBundle:Log')->findBy(array(), array("id"=>'DESC'), $length, $start);
        $allLogs = $em->getRepository('LogBundle:Log')->findBy(array(), array("id"=>'DESC'));

        $data = array();

        foreach($logs as $log) {
            /** @var Log $log */

            $data[] = array(
                "level" => $log->getLevel(),
                "action" => $log->getAction(),
                "details" => $log->getMessage(),
                "date" => $log->getDate()->format('m/d/Y H:i:s a'),
                "user" => $log->getUser(),
                "labelColor" => $log->getLabelColor(),
                "showRoute" => $this->generateUrl('log_show', array('id' => $log->getId())),
            );
        }

        $options = array(
            "draw" => intval($draw),
            "recordsTotal" => count($allLogs), // Without filtering
            "recordsFiltered" => count($allLogs), // With Filtering
            "data" => $data
        );
        return new JsonResponse($options);

    }
}
