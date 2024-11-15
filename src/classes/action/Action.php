<?php

namespace iutnc\nrv\action;

use iutnc\nrv\exception\ActionException;

abstract class Action {

    protected ?string $http_method = null;
    protected ?string $hostname = null;
    protected ?string $script_name = null;

    public function __construct(){
        $this->http_method = $_SERVER['REQUEST_METHOD'];
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->script_name = $_SERVER['SCRIPT_NAME'];
    }

    /**
     * @throws ActionException
     */
    public function execute() : string {
        if($this->http_method === "POST") {
            $this->verifPost();
            return $this->executePost();
        }
        $this->verifGet();
        return $this->executeGet();
    }

    /**
     * @throws ActionException
     */
    private function verifGet(): void {
        $sqlKeywords = '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|OR|AND|--|#|;)\b/i';
        foreach($_GET as $get) {
            if(!filter_var($get, FILTER_VALIDATE_INT)) {    // Aucun problème si c'est un entier
                if(preg_match($sqlKeywords, $get)) {
                    throw new ActionException("Invalid GET parameter ou danger d'injection SQL detecter");
                }
            }
        }
    }

    /**
     * @throws ActionException
     */
    private function verifPost() : void {
        $sqlKeywords = '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|OR|AND|--|#|;)\b/i';
        foreach($_POST as $key => $post) {
            $conditions = [
                (filter_var($post, FILTER_VALIDATE_URL) && str_contains($post, "youtube")),
                preg_match($sqlKeywords, $post)
            ];
            if(!filter_var($post, FILTER_VALIDATE_INT)) {    // Aucun problème si c'est un entier
                foreach ($conditions as $condition)  if ($condition) throw new ActionException("Invalid POST parameter ou danger d'injection SQL detecter");
            }
            $_POST[$key] = filter_var($post, FILTER_SANITIZE_SPECIAL_CHARS);
        }
    }
    abstract function executeGet(): string;
    abstract function executePost(): string;

}