<?php
class JSHelper extends Helper {
    private $buffer = '';

    public function bindOnClick($elementId, $callback) {
        $js = '$("#%s").live("click", %s);';
        $this->append(sprintf($js, $elementId, $callback));
    }

    public function template($views) {
        foreach($views as $viewItem) {
            $view = new View($this->context);
            $html = $view->renderView($viewItem, array(), true);
            $html = json_encode($html);
            $this->append("p1mvc.loadView('$viewItem', $html);");
        }
    }

    public function append($js) {
        $this->buffer.= "$js\n";
    }

    public function dump() {
        return "<script type='text/javascript'>$(document).ready(function () {\n{$this->buffer}\n});</script>";
    }
}
