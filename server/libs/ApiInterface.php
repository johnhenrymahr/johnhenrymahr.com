<?php
namespace JHM;

interface ApiInterface
{
    public function init($request);

    public function handler($id, ApiHandlerInterface $handler);

    public function respond();

}
