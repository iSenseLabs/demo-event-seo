<?php

class ControllerModuleEventSeo extends Controller {
    public static $is_route_decoded = false;
    public static $is_rewrite_added = false;

    public function add_url_rewrite($route, $args) {
        if (!self::$is_rewrite_added) {
            $this->url->addRewrite($this);
            self::$is_rewrite_added = true;
        }
    }

    public function decode($route, $args) {
        if (self::$is_route_decoded || !isset($this->request->get['_route_']) || (isset($this->request->get['route']) && $this->request->get['route'] != 'error/not_found')) return;

        $parts = preg_split('@(?<!/fs)/(?!fs/)@', $this->request->get['_route_']);

        $route = array();
        $is_route_set = false;
        
        foreach ($parts as $part) {
            if (!$is_route_set && strpos($part, ':') !== false) {
                $this->request->get['route'] = implode('/', $route);
                $is_route_set = true;
            }

            if ($is_route_set) {
                $query_var_parts = explode(':', str_replace('/fs/', '/', $part));
                $query_var_name = array_shift($query_var_parts);
                $this->request->get[$query_var_name] = implode(':', $query_var_parts);
            } else {
                $route[] = $part;
            }
        }

        if (!$is_route_set) {
            $this->request->get['route'] = implode('/', $route);
            $is_route_set = true;
        }

        self::$is_route_decoded = true;

        $action = new Action($this->request->get['route']);

        $result = $action->execute($this->registry, array($args));

        if ($result) {
            return $result;
        } else if ($this->response->getOutput()) {
            $this->response->output();exit;
        }
    }

    public function rewrite($link) {
        if (strpos($link, 'index.php?route=') === false) return $link;
        $url_info = parse_url(str_replace('&amp;', '&', $link));

        if (empty($url_info['query'])) return $link;

        $url = '';
        $data = array();
        parse_str($url_info['query'], $data);

        foreach ($data as $key=>$value) {
            if ($key == 'route') {
                $url .= '/' . $value;
                continue;
            }

            $url .= '/' . $key . ':' . str_replace('/', '/fs/', $value);
        }

        return $url_info['scheme'] . '://' . $url_info['host'] . (isset($url_info['port']) ? ':' . $url_info['port'] : '') . str_replace('/index.php', '', $url_info['path']) . $url;
    }
}
