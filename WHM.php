<?php

/**
 * Class WHM
 *
 * @author Vagner Cardoso <vagnercardosoweb@gmail.com>
 */
class WHM
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $type = 'json-api';

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * WHM constructor.
     *
     * @param string $host
     * @param string $user
     * @param string $hash
     *
     * @throws \Exception
     */
    public function __construct($host, $user, $hash)
    {
        if (!isset($host, $user, $hash)) {
            throw new Exception("Existe parametros em branco, favor verifique.");
        }

        $this->host = "{$host}/{$this->type}";
        $this->user = $user;
        $this->hash = preg_replace('(\r|\n)', '', $hash);

        // Monta a header de autenticação
        $this->setHeaders([
            "Authorization: WHM {$this->user}:{$this->hash}"
        ]);
    }

    /**
     * Suspende e-mail da conta
     *
     * @param string $user
     *
     * @return mixed
     */
    public function suspend_outgoing_email($user)
    {
        $params = array(
            'api.version' => '1',
            'user' => $user,
        );

        return $this->request('get', "{$this->host}/suspend_outgoing_email", $params);
    }

    /**
     * Volta e-mail da conta
     *
     * @param string $user
     *
     * @return mixed
     */
    public function unsuspend_outgoing_email($user)
    {
        $params = array(
            'api.version' => '1',
            'user' => $user,
        );

        return $this->request('get', "{$this->host}/unsuspend_outgoing_email", $params);
    }

    /**
     * Seta os headers da requisição
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers = [])
    {
        foreach ((array) $headers as $header) {
            $this->headers[] = $header;
        }

        return $this;
    }

    /**
     * Recupera os headers da requisição
     *
     * @return array
     */
    public function getHeaders()
    {
        // Defaults headers
        $this->headers[] = "User-Agent: VCWeb Create cURL";
        $this->headers[] = "Accept-Charset: utf-8";
        $this->headers[] = "Accept-Language: pt-br;q=0.9,pt-BR";

        return $this->headers;
    }

    /**
     * Emula o http_build_query do php com arrays multimensional
     *
     * @param array  $params
     * @param string $prefix
     *
     * @return array|string
     */
    protected function http_build_curl(array $params, $prefix = null)
    {
        if (!is_array($params)) {
            return $params;
        }

        $build = [];

        foreach ($params as $key => $value) {
            if (is_null($value)) {
                continue;
            }

            if ($prefix && $key && !is_int($key)) {
                $key = "{$prefix}[{$key}]";
            } elseif ($prefix) {
                $key = "{$prefix}[]";
            }

            if (is_array($value)) {
                $build[] = $this->http_build_curl($value, $key);
            } else {
                $build[] = $key . '=' . urlencode($value);
            }
        }

        return implode('&', $build);
    }

    /**
     * Monta toda requisição a ser enviada e trada os dados
     *
     * @param string $method
     * @param string $endPoint
     * @param array  $params
     *
     * @return mixed
     */
    protected function request($method, $endPoint, $params = [])
    {
        $method = mb_strtoupper($method, 'UTF-8');

        // Verifica se a data e array e está passada
        if (is_array($params) && !empty($params)) {
            $params = $this->http_build_curl($params);
        }

        // Trata a URL se for GET
        if ($method === 'GET') {
            $separator = '?';
            if (strpos($endPoint, '?') !== false) {
                $separator = '&';
            }

            $endPoint .= "{$separator}{$params}";
        }

        // Inicia o cURL
        $curl = curl_init();

        // Monta os options do cURL
        $options = [
            CURLOPT_URL => $endPoint,
            CURLOPT_HTTPHEADER => $this->getHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 80,
            CURLOPT_CUSTOMREQUEST => $method,
        ];

        // Verifica se não e GET e passa os parametros
        if ($method !== 'GET') {
            $options[CURLOPT_POSTFIELDS] = $params;
        }

        // Verifica se e post e seta como post
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
        }

        // Passa os options para o cURL
        curl_setopt_array($curl, $options);

        // Resultados
        $response = curl_exec($curl);
        $error = curl_errno($curl);
        curl_close($curl);

        // Verifica se tem erros
        if ($error) {
            return $error;
        }

        // Retorna a resposta
        return $response;
    }
}
