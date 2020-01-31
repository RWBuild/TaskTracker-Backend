<?php

namespace App\Exceptions;

use Exception;

class TaskTrackerException extends Exception
{
    /**
     * will contain the message and other attribute
     * @var Array
     */
    protected $data = [];

    /**
     * will contain the error status
     * default = 400
     * @var int
     */
    protected $status = 400;

    public function __construct($message,$status = null)
    {
        parent::__construct($message);
        $this->status = !$status? $this->status : $status;
    }

    public function render()
    {
        return $this->buildResponse();
    }

    public function withData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function withStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function buildResponse()
    {
        return response()->json($this->buildData(), $this->status);
    }

    public function buildData()
    {
        if ($this->data == []) {
            return [
                'success' => false,
                'message' => $this->getMessage()
            ];
        }

        return [
            'success' => false,
            'message' => $this->getMessage(),
            'data' => $this->data
        ];
    }
}
