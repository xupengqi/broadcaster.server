<?php
class FormHelper extends Helper {
    private $buffer;
    private $verb;
    private $model;
    private $action;
    private $submitId;

    public function begin($verb, $model, $action = '', $attr = array()) {
        $config = $this->context->getConfig();

        $this->verb = $verb;
        $this->model = $model;
        $this->action = $action;
        $formStr = "<form %s>";

        $attr['method'] = $verb;
        $attr['id'] = $this->getFormId($verb, $model, $action);
        $attr['action'] = $config['appPath'].$model.'/'.$action;

        $this->buffer = sprintf($formStr, $this->getAttrStr($attr));
        $this->submitId = '';
        return $this;
    }

    public function end() {
        $this->buffer.= '</form>';
        echo $this->buffer;
    }

    public function input($name, $type = 'text', $attr = array()) {
        $input = "<input %s />";

        $attr['id'] = $this->getInputId($name);
        if(!isset($attr['name'])) {
            $attr['name'] = $name;
        }
        $attr['type'] = $type;

        switch($type) {
            case 'hidden':
                break;
            case 'submit':
                $this->submitId = $attr['id'];
                unset($attr['name']);
                break;
            default:
                $input = $this->label($name, $input);
                break;
        }

        $this->buffer.= sprintf($input, $this->getAttrStr($attr));
        return $this;
    }

    public function submit($name, $attr = array()) {
        $attr['value'] = $name;
        return $this->input($name, 'submit', $attr);
    }

    public function select($name, $choices = array(), $attr = array()) {
        $select = "<select %s>";

        foreach ($choices as $key=>$val) {
            $select.= "<option value='$key'>$val</option>";
        }
        $select.= "</select>";

        $attr['id'] = $this->getInputId($name);
        $attr['name'] = $name;

        $this->buffer.= $this->label($name, sprintf($select, $this->getAttrStr($attr)));
        return $this;
    }

    public function useAjax($callback) {
        $this->context->loadHelpers(array('js'));
        $this->context->helpers['js']->bindOnClick($this->submitId, $callback);
        return $this;
    }

    private function label($label, $content) {
        return "<div class='input'><label>$label</label>$content</div>";
    }

    private function getAttrStr($attr = array()) {
        $attrStr = '';
        //error_log('$attr:'.print_r($attr,true));
        foreach ($attr as $a=>$v) {
            $attrStr.= " $a='{$v}'";
        }

        return trim($attrStr, ' ');
    }

    protected function getInputId($name) {
        return "{$this->model}_{$this->action}_{$this->verb}_{$name}";
    }
    
    protected function getFormId($verb, $model, $action) {
        return trim("{$verb}_{$model}_{$action}_form", '_');
    }
}
