<!DOCTYPE html>

<html>
	<head>
		<title>OUTRAG3bot documentation</title>
		
		<style type="text/css">
			body
			{
				width: 65%;
				
				margin-left: auto;
				margin-right: auto;
				
				font-family: "Tahoma";
			}
			
			.doc-overview
			{
				padding: 20px;
				background: #F6F9F3;
				border: 1px solid #CCC;
				margin-bottom: 30px;
			}
			
			.doc-item
			{
				border: 1px solid #CCC;
				border-left: 15px solid #CCC;
				
				padding: 20px;
				margin-bottom: 30px;
				
				font-size: 78%;
			}
			
			.doc-item h3
			{
				margin-top: 0px;
				font-family: monospace;
				font-size: 16px;
			}
			
			.doc-item .type
			{
				width: 110px;
				display: inline-block;
				
				font-family: monospace;
				font-size: 12px;
				text-align: right;
				
				margin-right: 2px;
				
				color: #007700;
			}
			
			.doc-item .name
			{
				width: 110px;
				display: inline-block;
				
				font-family: monospace;
				font-size: 12px;
				text-align: left;
				
				margin-right: 2px;
				
				color: #0000BB;
			}
			
			.doc-item .example
			{
				font-size: 12px;
				
				background: #F6F9F3;
				border: 1px solid #CCC;
				
				padding: 20px;
				margin-bottom: 20px;
			}
			
			.doc-item .example:last-child
			{
				margin-bottom: 0px;
			}
			
			.doc-item.method
			{
				border: 1px solid #F33;
				border-left: 15px solid #F33;
				background-color: #FDD;
			}
			
			.doc-item.property
			{
				border: 1px solid #33F;
				border-left: 15px solid #33F;
				background-color: #CCF;
			}
			
			.doc-item.event
			{
				border: 1px solid #AAE5AA;
				border-left: 15px solid #AAE5AA;
				background-color: #DDFFE3;
			}
			
			code
			{
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<header>
			<h1>OUTRAG3bot documentation</h1>
		</header>
		
		<section class="doc-group methods">
			<?php foreach($_methods as $class => $methods): ?>
				<section class="method-class-list">
					<h2>Class description for <?php echo $class ?></h2>
					
					<article class="doc-overview">
						<?php
							# define the header
							$definition = "class ".$class.PHP_EOL."{".PHP_EOL;
							
							$_overview_properties = [];
							$_overview_methods = [];
							
							foreach($methods as $title => $method)
							{
								if($method->type == "property")
									$_overview_properties[$title] = $method;
								
								if($method->type == "method")
									$_overview_methods[$title] = $method;
							}
							
							if($_overview_properties)
							{
								$definition .= "\t/* properties */".PHP_EOL;
								
								foreach($_overview_properties as $title => $method)
									$definition .= "\tpublic <<<<<$".$title.">>>>>;".PHP_EOL;
								
								$definition .= PHP_EOL;
							}
							
							if($_overview_methods)
							{
								$definition .= "\t/* methods */".PHP_EOL;
								
								foreach($_overview_methods as $title => $method)
								{
									$optional = 0;
									$arguments = [];
									
									foreach($method->parameters as $item)
									{
										if($item->optional)
											++$optional;
										
										$arguments[] = ($item->optional ? "[ " : "")."$".$item->name.($item->default ? " = ".json_encode($item->default) : "");
									}
									
									$arguments = implode(", ", $arguments).($item->optional ? " ".str_repeat("]", $optional) : "");
									
									$definition .= "\tpublic function <<<<<".$title.">>>>>(".$arguments.");".PHP_EOL;
								}
							}
							
							$definition .= "}";
							$definition = custom_highlight_string($definition);
							
							$pattern = "/".preg_quote('&lt;&lt;&lt;&lt;&lt;</span><span style="color: #0000BB">', '/')."(.*?)".preg_quote('</span><span style="color: #007700">&gt;&gt;&gt;&gt;&gt;', '/')."/";
							
							$callback = function($matches) use ($class)
							{
								return '<span style="color: #0000BB"><a href="#'.method_to_hash($class, $matches[1]).'" data-class="'.$class.'" data-method="'.$matches[1].'">'.$matches[1].'</a></span>';
							};
							
							echo preg_replace_callback($pattern, $callback, $definition);
						?>
					</article>
					
					<?php foreach($methods as $title => $method): ?>
						<?php if($method->type == "method"): ?>
							<article class="doc-item method">
								<?php
									$optional = 0;
									$arguments = [];
									
									foreach($method->parameters as $item)
									{
										if($item->optional)
											++$optional;
										
										$arguments[] = ($item->optional ? "[ " : "")."$".$item->name.($item->default ? " = ".json_encode($item->default) : "");
									}
									
									$arguments = implode(", ", $arguments).($item->optional ? " ".str_repeat("]", $optional) : "");
								?>
								
								<h3>
									<a name="<?php echo method_to_hash($class, $title) ?>">
										<?php echo $method->metadata->class ?>::<?php echo $method->metadata->method ?>(<?php echo $arguments ?>)
									</a>
								</h3>
								
								<?php if(!empty($method->comments)): ?>
									<h4>Description</h4>
									
									<p>
										<?php echo nl2br($method->comments) ?>
									</p>
								<?php endif ?>
								
								<h4>Accepted arguments</h4>
								
								<?php if(!empty($method->parameters)): ?>
									<ol>
										<?php foreach($method->parameters as $item): ?>
											<li>
												<span class="type">
													<?php echo $item->type ?>
												</span>
												
												<span class="name">
													$<?php echo $item->name ?>
												</span>
												
												<span class="description">
													<?php echo $item->description ?>
												</span>
											</li>
										<?php endforeach ?>
									</ol>
								<?php else: ?>
									<p>This method does not take any arguments.</p>
								<?php endif ?>
								
								<?php if(!empty($method->examples)): ?>
									<h4>Examples</h4>
									
									<?php foreach($method->examples as $index => $example): ?>
										<div class="example <?php echo strtolower($example->type) ?>"><?php echo custom_highlight_string($example->contents) ?></div>
									<?php endforeach ?>
								<?php endif ?>
							</article>
						<?php else: ?>
							<article class="doc-item property">
								<h3>
									<a name="<?php echo method_to_hash($class, $title) ?>">
										<?php echo $method->metadata->class ?>::$<?php echo $method->metadata->property ?>
									</a>
								</h3>
								
								<?php if($method->comments): ?>
									<h4>Description</h4>
									
									<p>
										<?php echo nl2br($method->comments) ?>
									</p>
								<?php endif ?>
							</article>
						<?php endif ?>
					<?php endforeach ?>
				</section>
			<?php endforeach ?>
		</section>
		
		<section class="doc-group events">
			<h2>Events</h2>
			
			<?php foreach($_events as $title => $event): ?>
				<article class="doc-item event">
					<h3><?php echo $event->metadata->event ?></h3>
					
					<?php if($event->comments): ?>
						<h4>Description</h4>
						
						<p>
							<?php echo nl2br($event->comments) ?>
						</p>
					<?php endif ?>
					
					<h4>Information</h4>
					
					<ul>
						<li>
							<strong>Numeric:</strong> <?php echo $event->metadata->method ?>
						</li>
						
						<li>
							<strong>Handler:</strong> <?php echo $event->metadata->onevent ?>
						</li>
					</ul>
					
					<h4>Arguments passed to handler</h4>
					
					<?php if($event->supplies): ?>
						<ol>
							<?php foreach($event->supplies as $item): ?>
								<li>
									<span class="type">
										<?php echo $item->type ?>
									</span>
									
									<span class="name">
										$<?php echo $item->name ?>
									</span>
									
									<span class="description">
										<?php echo $item->description ?>
									</span>
								</li>
							<?php endforeach ?>
						</ol>
					<?php else: ?>
						<p>No arguments passed to the handler.</p>
					<?php endif ?>
					
					<h4>Examples</h4>
					
					<?php
						$arguments = [];
						
						foreach($event->supplies as $item)
							$arguments[] = "$".$item->name;
						
						$arguments = implode(", ", $arguments);
					?>
					
					<div class="example">
						<?php
						
						$example = <<<EOL
class ExampleScript extends OUTRAGEbot\Script
{
	public function {$event->metadata->onevent}({$arguments})
	{
		...
	}
}
EOL;
						echo custom_highlight_string($example);
						?>
					</div>
					
					<div class="example">
						<?php
						
						$onevent = strtolower($event->metadata->event);
						
						$example = <<<EOL
class ExampleScript extends OUTRAGEbot\Script
{
	public function init()
	{
		\$this->on("{$onevent}", function({$arguments})
		{
			...
		});
	}
}
EOL;
						echo custom_highlight_string($example);
						?>
					</div>
				</article>
			<?php endforeach ?>
		</section>
	</body>
</html>