<?php
// source: C:\xampp\Webdocs8\htdocs\oak\app/Views/index.latte

use Latte\Runtime as LR;

class Templateb6854e2cf5 extends Latte\Runtime\Template
{

	function main()
	{
		extract($this->params);
		?><h1>Hello</h1><?php
		return get_defined_vars();
	}

}
