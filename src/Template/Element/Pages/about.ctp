<?php
/**
 * The contents of this page are returned by the API endpoint /pages/about
 */
?>
<h1>
	About Muncie Events
</h1>

<p>
	Muncie Events is a comprehensive event promotion tool provided to the city of
    Muncie, Indiana by the <a href="https://munciearts.org">Muncie Arts and Culture Council</a> and the
    <a href="https://bsu.edu/cber">Center for Business and Economic Research</a> at Ball State University.
</p>
<p>
	This service is guided by the following principles:
</p>
<ul>
	<li>
		<strong>Free:</strong>
		A community calendar should be free for everyone to view and contribute to.
	</li>
	<li>
		<strong>Democratic:</strong>
		The contents and function of a community calendar should be determined by the community.
	</li>
	<li>
		<strong>Egalitarian:</strong>
		All demographics, event types, and genres of expression should be promoted
		equally in order to break down social barriers and unite disparate components of the community.
	</li>
	<li>
		<strong>Decentralized:</strong>
		A decentralized, multifaceted approach to distributing a database of event
		information allows it to reach a wider audience by a variety of methods and with the
		participation of community partners.
	</li>
	<li>
		<strong>Evolving:</strong>
		The development of an online community calendar should be an ongoing process,
		guided by the community that it serves and the advancement of web technology.
	</li>
</ul>

<h2>
	Where Credit's Due
</h2>

<?php
    $credits = [
        'People' => [
            '<a href="mailto:graham@phantomwatson.com">Graham Watson</a>' => 'Web Developer, Administrator',
            'Erica Dee Fox' => 'Web Developer',
            'Benjamin Easley' => 'Graphic Designer',
            'Nicholas Boyum' => 'Artist (map of Muncie background image)'
        ],
        'Organizations' => [
            '<a href="https://munciearts.org">Muncie Arts and Culture Council</a>' => '',
            '<a href="http://bsu.edu/cber">Center for Business and Economic Research</a>' => ''
        ],
        'Software' => [
            '<a href="http://cakephp.org">CakePHP</a>' => 'Site framework',
            '<a href="http://jquery.com/">jQuery</a>' => 'Javascript framework',
            '<a href="http://jqueryui.com/">jQuery UI</a>' => 'User interface magic',
            '<a href="http://dimsemenov.com/plugins/magnific-popup/">Magnific Popup</a>' => 'Elegant media popups',
            '<a href="http://www.digitalmagicpro.com/jPicker/">jPicker</a>' => 'Color picker',
            '<a href="http://recaptcha.net/">reCAPTCHA</a>' => 'Spam defense',
            '<a href="http://google.com/analytics">Google Analytics</a>' => 'Traffic analysis',
            '<a href="http://www.jetbrains.com/phpstorm/">PhpStorm</a>' => 'IDE of choice',
            '<a href="http://travis-ci.org">Travis CI</a>' => 'Testing'
        ]
    ];
?>

<ul style="margin: 0; padding: 0;">
	<?php foreach ($credits as $category => $members): ?>
		<li style="
            border-bottom: 1px solid #555;
            color: #555;
            font-size: 110%;
            letter-spacing: 7px;
            list-style-type: none;
        ">
            <?php echo $category ?>
        </li>
		<?php foreach ($members as $name => $position): ?>
			<li style="
                list-style-type: none;
                margin-left: 0;
                margin-right: 0;
                display: block;
            ">
                <div style="
                    background-color: #FFF;
                    font-weight: bold;
                    margin-bottom: -3px;
                    padding-right: 5px;
                    position: relative;
                    top: 4px;
                ">
                    <?php echo $name ?>
                </div>
                <div style="
                    background-color: #FFF;
                    color: #555;
                    padding-left: 0;
                    position: relative;
                    right: -2px;
                    top: 4px;
                ">
                    <?php echo $position ?>
                </div>
                <br />
            </li>
		<?php endforeach; ?>
		<li style="
		    border: none;
		    list-style-type: none;
            margin-left: 0;
            margin-right: 0;
            display: block;
        ">&nbsp;</li>
	<?php endforeach; ?>
</ul>
