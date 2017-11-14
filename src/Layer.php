<?php

namespace LianzhView;

/**
 * 视图层
 */
class Layer
{

    /**
     * 该层所属的视图对象
     *
     * @var \LianzhView\Renderer
     */
    public $renderer;

    /**
     * 父层对象
     *
     * @var \LianzhView\Layer
     */
    public $parent;

    /**
     * 视图名称
     *
     * @var string
     */
    public $viewname;

    /**
     * 该层的内容
     *
     * @var string
     */
    public $contents;

    /**
     * 该层区块的内容
     *
     * @var array
     */
    public $blocks = [];

    /**
     * 该层的区块
     *
     * @var array
     */
    private $blockStack = [];

    /**
     * 预定义的区块
     *
     * @var array
     */
    private $predefinedBlocks = [];

    /**
     * 构造函数
     *
     * @param Renderer $renderer
     * @param string $viewname
     */
    public function __construct(Renderer $renderer, $viewname)
    {
        $this->renderer = $renderer;
        $this->viewname = $viewname;
    }

    /**
     * 返回该层的顶级层（最底层的视图）
     *
     * @return \LianzhView\Layer
     */
    public function root()
    {
        return ($this->parent) ? $this->parent->root() : $this;
    }

    /**
     * 分析视图，并返回结果
     *
     * @param array $predefined_blocks
     */
    public function parse(array $predefined_blocks = [])
    {
        $this->predefinedBlocks = $predefined_blocks;

        ob_start();
        extract($this->renderer->vars);
        include $this->renderer->filename($this->viewname);
        $this->contents = ob_get_clean();

        $this->predefinedBlocks = null;
        foreach ($this->blocks as $block_name => $contents) {
            $search = "%_view_block.{$block_name}_%";
            if (strpos($this->contents, $search) !== false) {
                $this->contents = str_replace($search, $contents, $this->contents);
            }
        }
    }

    /**
     * 从指定层继承
     *
     * @param string $viewname
     */
    public function extend($viewname)
    {
        $this->parent = new Layer($this->renderer, $viewname);
    }

    /**
     *
     * 定义一个区块
     *
     * @param string $block_name
     * @param boolean $append
     */
    public function block($block_name, $append = false)
    {
        array_push($this->blockStack, array($block_name, $append));
        ob_start();
    }

    /**
     * 结束最后定义的一个区块
     */
    public function endblock()
    {
        list($block_name, $append) = array_pop($this->blockStack);
        $contents = ob_get_clean();
        $this->createBlock($contents, $block_name, $append);
    }

    /**
     * 定义一个空区块
     *
     * @param string $block_name
     * @param boolean $append
     */
    public function emptyBlock($block_name, $append = false)
    {
        $this->createBlock('', $block_name, $append);
    }

    /**
     * 载入一个视图片段
     *
     * @param string $viewname 视图片段名
     */
    public function element($viewname)
    {
        $__filename = $this->renderer->filename("_elements/{$viewname}");
        extract($this->renderer->vars);
        include $__filename;
    }

    /**
     * 完成一个区块
     *
     * @param string $contents
     * @param string $block_name
     * @param boolean $append
     */
    private function createBlock($contents, $block_name, $append)
    {
        if (isset($this->predefinedBlocks[$block_name])) {
            if ($append) {
                $contents .= $this->predefinedBlocks[$block_name];
            } else {
                $contents = $this->predefinedBlocks[$block_name];
            }
        }

        $this->blocks[$block_name] = $contents;
        echo "%_view_block.{$block_name}_%";
    }
}