<?php

namespace LianzhView;

use \InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Renderer
 * @package LianzhView
 *
 * Render PHP viewlayer scripts into a PSR-7 Response object
 */
class Renderer
{

	/**
     * @var string
     */
    public $dir;

    /**
     * @var string
     */
    public $viewname;

    /**
     * @var array
     */
    public $vars = [];

    /**
     * 构造函数
     *
     * @param string $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Render a view
     *
     * @param ResponseInterface $response
     * @param string 			$viewname
     * @param array 			$vars
     *
     * @return ResponseInterface
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function render(ResponseInterface $response, $viewname, array $vars = [])
    {
    	$this->vars = $vars;
        $this->viewname = $viewname;

        $output = $this->execute();

        $response->getBody()->write($output);

        return $response;
    }

    /**
     * @return string
     */
    private function execute()
    {
        $viewname = $this->viewname;
        $child = new Layer($this, $viewname);

        $error_reporting = ini_get('error_reporting');
        error_reporting($error_reporting & ~E_NOTICE);
        $child->parse();

        $layer = $child;
        while (($parent = $layer->parent) != null) {
            $parent->parse($layer->blocks);
            $layer = $parent;
        }

        error_reporting($error_reporting);
        return $child->root()->contents;
    }

    /**
     * find view file path
     *
     * @param string $viewname
     *
     * @return string
     */
    public function filename($viewname)
    {
        $filename = str_replace('.', DIRECTORY_SEPARATOR, $viewname) . '.phtml';
        return $this->dir . DIRECTORY_SEPARATOR . $filename;
    }

}