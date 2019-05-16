<?php
/**
 * @var AppView $this
 */

use App\View\AppView;
?>
<nav class="navbar navbar-toggleable-md navbar-inverse header">
    <button class="navbar-toggler navbar-toggler-left" type="button" data-toggle="collapse"
            data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false"
            aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <h1>
        <a href="/" class="navbar-brand nav-item logo title">
            <i id="logo-icon" class="icon-me-logo"></i><span>Muncie</span><span>Events</span>
        </a>
    </h1>
    <ul class="navbar-nav" id="med-nav">
        <li class="navbar-item">
            <ul id="mid-nav" class="navbar-nav">
                <?= $this->element('Header/links_secondary') ?>
            </ul>
        </li>
    </ul>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div>
            <div class="header-nav primary-nav">
                <?= $this->element('Header/links_primary') ?>
            </div>
            <div class="header-nav">
                <ul id="res-nav" class="navbar-nav">
                    <?= $this->element('Header/links_secondary') ?>
                </ul>
            </div>
        </div>
    </div>
    <ul class="navbar-nav" id="tagline-lg">
        <li class="navbar-item">
            <a class="navbar-brand logo">
                <?= $this->element('Header/tagline') ?>
            </a>
            <br />
        </li>
    </ul>
    <?= $this->element('Header/search') ?>
</nav>
