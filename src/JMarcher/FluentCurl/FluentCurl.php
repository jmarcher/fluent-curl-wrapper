<?php
/**
 * Created by PhpStorm.
 * User: gordo
 * Date: 26/05/16
 * Time: 03:21 PM
 */

namespace Marcher\FluentCurl;


class FluentCurl
{
    /**
     * @var resource
     */
    protected $connection;
    /**
     * @var null
     */
    protected $url;
    /**
     * @var
     */
    protected $method;
    /**
     * @var
     */
    protected $http_header;
    /**
     * @var
     */
    protected $must_return_transfer;
    /**
     * @var
     */
    protected $post_fields;
    /**
     * @var
     */
    protected $result;
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * FluentCurl constructor.
     */
    public function __construct($url = null)
    {
        $this->url = $url;
        $this->connection = curl_init($url);
    }

    /**
     * @return resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * An already initialized connection
     *
     * @param resource $connection
     * @return FluentCurl
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param null $url
     * @return FluentCurl
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     * @return FluentCurl
     */
    public function setMethod(int $method)
    {
        $this->method = $method;
        curl_setopt($this->connection, $method, true);

        return $this;
    }

    /**
     * @return FluentCurl
     */
    public function setPostMethod()
    {
        return $this->setMethod(CURLOPT_POST);
    }

    /**
     * @return FluentCurl
     */
    public function setPutMethod()
    {
        return $this->setMethod(CURLOPT_PUT);
    }

    /**
     * @return FluentCurl
     */
    public function setGetMethod()
    {
        return $this->setMethod(CURLOPT_HTTPGET);
    }

    /**
     * @return mixed
     */
    public function getHttpHeader()
    {
        return $this->http_header;
    }

    /**
     * @param mixed $http_header
     * @return FluentCurl
     */
    public function setHttpHeader($http_header)
    {
        $this->http_header = $http_header;
        curl_setopt($this->connection, CURLOPT_HTTPHEADER, $http_header);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMustReturnTransfer()
    {
        return $this->must_return_transfer;
    }

    /**
     * @param mixed $must_return_transfer
     * @return FluentCurl
     */
    public function setMustReturnTransfer(bool $must_return_transfer = true)
    {
        $this->must_return_transfer = $must_return_transfer;
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, $must_return_transfer);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostFields()
    {
        return $this->post_fields;
    }

    /**
     * @param mixed $post_fields
     * @return FluentCurl
     */
    public function setPostFields($post_fields, $shouldEncode = true)
    {
        $this->post_fields = $post_fields;
        curl_setopt($this->connection, CURLOPT_POSTFIELDS, $shouldEncode ? json_encode($post_fields) : $post_fields);

        return $this;
    }

    /**
     * @param bool $logErrors
     * @param bool $closeAfterExecution
     * @return $this
     */
    public function execute($logErrors = true, $closeAfterExecution = true)
    {
        $this->result = curl_exec($this->connection);
        if ($logErrors && curl_errno($this->connection)) {
            $this->errors[] = curl_error($this->connection);
        }
        if ($closeAfterExecution) {
            curl_close($this->connection);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function close()
    {
        curl_close($this->connection);
        return $this;
    }

    /**
     * Wraps all the post functionality into a single method
     *
     * @param string $url
     * @param array  $data
     * @param array  $headers
     */
    public function doPostRequest(string $url, array $data, array $headers = [])
    {
        $this->setUrl($url)
            ->setPostMethod()
            ->setPostFields($data)
            ->setMustReturnTransfer()
            ->setHttpHeader($headers)
            ->execute();
    }
}