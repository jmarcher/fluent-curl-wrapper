<?php
/**
 * Created by PhpStorm.
 * User: gordo
 * Date: 26/05/16
 * Time: 03:21 PM.
 */
namespace Marcher\FluentCurl;

class FluentCurl
{
    /**
     * @var resource
     */
    protected $connection;
    /**
     * @var string
     */
    protected $url;
    /**
     * @var int
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
     * Callback function to be called after a succesfully made request.
     *
     * @var callable
     */
    protected $callback;

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
     * An already initialized connection.
     *
     * @param resource $connection
     *
     * @return FluentCurl
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return FluentCurl
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
        $this->connection = curl_init($url);

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
     *
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
     *
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
     *
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
     *
     * @return FluentCurl
     */
    public function setPostFields(array $post_fields, bool $shouldEncode = true)
    {
        $this->post_fields = $post_fields;
        curl_setopt($this->connection, CURLOPT_POSTFIELDS, $shouldEncode ? json_encode($post_fields) : $post_fields);

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
     * Wraps all the post functionality into a single method.
     *
     * @param string $url
     * @param array  $data
     * @param array  $headers
     */
    public function doPostRequest(
      string $url,
      array $data,
      array $headers = [],
      bool $closeConnection = true
    ) {
        $this->setUrl($url)
            ->setPostMethod()
            ->setPostFields($data)
            ->setMustReturnTransfer()
            ->setHttpHeader($headers)
            ->execute();
        $this->_call($this->callback ?? function (FluentCurl $instance) {
        }, $this);
        if ($closeConnection) {
            $this->close();
        }

        return $this;
    }

    /**
     * @param bool $logErrors
     * @param bool $closeAfterExecution
     *
     * @return $this
     */
    public function execute(bool $logErrors = true, bool $closeAfterExecution = true)
    {
        $this->result = curl_exec($this->connection);
        if ($logErrors && curl_errno($this->connection)) {
            $this->errors[] = curl_error($this->connection);
        }

        if (filter_var(
            $this->getInfo(CURLINFO_HTTP_CODE),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 100, 'max_range' => 399]])
        ) {
            $this->_call($this->callback ?? function (FluentCurl $instance) {
            }, $this);
        }


        if ($closeAfterExecution) {
            curl_close($this->connection);
        }

        return $this;
    }

    /**
     * Get information regarding a specific transfer.
     *
     * @param null|int $opt
     *
     * @return array|string
     */
    public function getInfo($opt = null)
    {
        if (null === $opt) {
            return curl_getinfo($this->connection);
        }

        return curl_getinfo($this->connection, $opt);
    }

    /**
     * @return FluentCurl
     */
    public function setPostMethod()
    {
        return $this->setMethod(CURLOPT_POST);
    }

    /**
     * @param callable $callback
     *
     * @return $this
     */
    public function withCallback(callable $callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Call callable function.
     *
     * @param callable $function
     * @param mixed    $params
     *
     * @return void
     */
    private function _call(callable $function, ...$params)
    {
        if (is_callable($function)) {
            call_user_func_array($function, $params);
        }
    }

    /**
     * Returns true if the array of errors is bigger than one.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Returns an array with the requests errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
