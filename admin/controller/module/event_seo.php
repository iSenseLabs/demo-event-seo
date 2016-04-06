<?php

class ControllerModuleEventSeo extends Controller {
    public function index() {
        $this->response->redirect($this->request->server['HTTP_REFERER']);
    }

    public function install() {
        $this->load->model('extension/event');
        $this->model_extension_event->addEvent('event_seo', 'catalog/controller/*/before', 'module/event_seo/decode');
        $this->model_extension_event->addEvent('event_seo', 'catalog/model/*/before', 'module/event_seo/add_url_rewrite');
    }

    public function uninstall() {
        $this->load->model('extension/event');
        $this->model_extension_event->deleteEvent('event_seo');
    }
}
