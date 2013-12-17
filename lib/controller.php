<?php
class Controller extends MVC {
    public function render() {
        // default layout and view
        $this->context->setLayout('default');
        $this->context->setView('header', 'defaultHeader');
        $this->context->setView('footer', 'defaultFooter');
        $this->context->setView('body', $this->context->controllerName);

        // override default layout and view
        if(isset($this->layout)) {
            $this->context->setLayout($this->layout);
        }
        if(isset($this->views)) {
            $this->context->setViews($this->views);
        }

        // call controller
        $action = $this->context->getAction();
        $data = $this->$action($this->context->getParams());

        // render view
        $view = new View($this->context);
        $view->render($data);
    }
}