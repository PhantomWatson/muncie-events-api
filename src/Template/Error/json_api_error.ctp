<?php
    /** @var \Cake\View\View $this */
    $this->response = $this->response->withHeader('Content-Type', 'application/vnd.api+json');
    echo json_encode($errors, JSON_PRETTY_PRINT);
