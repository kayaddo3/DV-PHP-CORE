<?php

namespace App\Http\Controllers;

use App\Helpers\DevlessHelper;
use JsonRPC\Server;
use App\Helpers\Helper;
use App\Helpers\Response;

class RpcController extends Controller
{
    /**
     * Relay rpc request to ActionClass.
     *
     * @param  $payload
     *
     * @return \Illuminate\Http\Response
     */
    public function index($payload)
    {
        $service = $payload['service_name'];
        $method = Helper::query_string()['action'][0];

        // the service name devless is a reserved name
        $serviceMethodPath = ($service == config('devless')['name']) ?
                            config('devless')['system_class'] :
                            config('devless')['views_directory'].$service.'/ActionClass.php';

        if (file_exists($serviceMethodPath)) {
            include_once $serviceMethodPath;
        } else {
            return Response::respond(604);
        }

        $server = new Server();

        $class = new \ReflectionClass($service);

        DevlessHelper::rpcMethodAccessibility($class, $method);
        $server->getProcedureHandler()->withClassAndMethod($service, $service, $method);

        return  Response::respond(637, '', json_decode($server->execute()));
    }
}
