<?php

class ApiTest extends BrowserKitTestCase
{
    public function testGetMyTokenAPI(): void
    {
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345'])
            ->seeJson([
                'message' => 'success',
            ]);

        $this->assertEquals(200, $this->response->status());
    }

    public function testGetCampaignList(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_campaign_list',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testGetFilterQuestion(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_filter_type_question',
            ['lead_reactor_token' => $responseToken]);

        $this->assertEquals(200, $this->response->status());

        echo $this->response->getContent();
    }

    public function testGetCampaignContent(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_campaign_content',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testGetCampaignContentPhp(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_campaign_content_php',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testGetCampaignLongContent(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_campaign_long_content',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testGetStackCampaignContentPhp(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_stack_campaign_content_php',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testGetCampaignStackContent(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_campaign_stack_content',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testGetHighPayingListCampaigns(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_high_paying_list_campaigns',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testGetHighPayingContent(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_high_paying_content',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testGetHighPayingContentAjax(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('get_high_paying_content',
            ['lead_reactor_token' => $responseToken])
            ->see('Trying to get property of non-object');
    }

    public function testZipChecker(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('zip_checker',
            ['lead_reactor_token' => $responseToken])
            ->see('Please provide a valid US Zip Code');

        $this->assertEquals(200, $this->response->status());
    }

    public function testZipDetails(): void
    {
        //get the token
        $this->post('/getMyToken',
            [],
            ['user_email' => 'ariel@engageiq.com', 'user_password' => '12345']);

        $response = json_decode($this->response->getContent());
        $responseToken = $response->token;

        $this->get('zip_details',
            ['lead_reactor_token' => $responseToken]);

        $this->assertEquals(200, $this->response->status());
    }
}
