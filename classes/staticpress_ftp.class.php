<?php
class StaticPress_FTP {
    private $options = array();
    private $client;

    function __construct($options = array()){
        $this->options = $options;
        if (! $this->options['disabled']) {
            add_action('StaticPress::file_put', array($this, 'file_put'), 10, 2);
        }
    }

    public function file_put($file_dest, $url){
        $this->upload($file_dest);
    }

    private function upload($file_path){
        if (! file_exists($file_path)){ return false; }
        $remote_path = str_replace(static_press_admin::static_dir(), '', $file_path);
        $result = $this->client()->upload($file_path, $remote_path);
        if (function_exists('dbgx_trace_var')) {
            dbgx_trace_var($result);
        }
        return $result;
    }

    private function client(){
        if (isset($this->client) && $this->client->isConnected()) {
            return $this->client;
        }
        $options = array();
        if (isset($this->options['port'])) { $options['port'] = $this->options['port']; }
        if (isset($this->options['pasv'])) { $options['pasv'] = $this->options['pasv']; }
        if (isset($this->options['ssl']))  { $options['ssl']  = $this->options['ssl']; }
        $this->client = new StaticPress_FTPClient(
            $this->options['host'],
            $this->options['user'],
            $this->options['pass'],
            $options
        );
        $this->client->connect();
        return $this->client;
    }
}
