
    
    /**
     * 
     * Oauth authorization.
     * 
     * @var \Aura\Http\OAuth
     * 
     */
    protected $oauth;


    /**
     * 
     * Set OAuth authentication.
     *
     * @param Aura\Http\OAuth $oauth
     *
     * @return Aura\Http\Request This object.
     *
     */
    public function setOAuth(OAuth $oauth)
    {
        $this->oauth = $oauth;
        return $this;
    }
    

    protected function prepareOAuthAuthorization()
    {
        if ($this->oauth) {
            return;
        }

        $params = array();

        // include the GET query in the signature base string
        if($this->uri->query) {
            $params += $this->uri->query;
        }

        // only application/x-www-form-urlencoded content is included in
        // the signature base string
        if (is_array($this->content) && 
            'application/x-www-form-urlencoded' == $this->content_type) {
            
            $params += $this->content;
        }
        
        switch ($this->oauth->getAuthorizationMethod())
        {
            case OAuth::HTTP:
                $this->headers['Authorization'] = // todo http auth over write?
                    $this->oauth->signRequest($url, $this->method, $params);
                break;

            case OAuth::GET:
                $this->uri->query += 
                    $this->oauth->signRequest($url, $this->method, $params);
                break;

            case OAuth::POST:
                 $this->content   += 
                    $this->oauth->signRequest($url, $this->method, $params);
                break;
        }
    }