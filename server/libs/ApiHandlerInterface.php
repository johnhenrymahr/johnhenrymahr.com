<?php
namespace JHM;

use Symfony\Component\HttpFoundation\Request;

interface ApiHandlerInterface
{
    /**
     * process request object
     * @param  Request $request [description]
     * @return boolean
     */
    public function process(Request $request);

    /**
     * get http status
     * @return integer HTTP_STATUS
     */
    public function status();

    /**
     * get json body
     * @return Array
     */
    public function body();
}
