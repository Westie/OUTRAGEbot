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
				
				border: 1px solid #DDD;
				
				background-color: #EFEEFF;
				
				padding: 20px;
				margin-bottom: 20px;
			}
			
			.doc-item .example:last-child
			{
				margin-bottom: 0px;
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
					<h2>Class: <?php echo $class ?></h2>
					
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
								
								<h3><?php echo $method->metadata->class ?>::<?php echo $method->metadata->method ?>(<?php echo $arguments ?>)</h3>
								
								<?php if($method->comments): ?>
									<h4>Description</h4>
									
									<p>
										<?php echo nl2br($method->comments) ?>
									</p>
								<?php endif ?>
								
								<h4>Accepted arguments</h4>
								
								<?php if($method->parameters): ?>
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
							</article>
						<?php else: ?>
							<article class="doc-item property">
								<h3><?php echo $method->metadata->class ?>::$<?php echo $method->metadata->property ?></h3>
								
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
				<article class="doc-item">
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
<?php

class ExampleScript extends OUTRAGEbot\Script
{
	public function {$event->metadata->onevent}({$arguments})
	{
		...
	}
}

?>
EOL;
						highlight_string($example);
						?>
					</div>
					
					<div class="example">
						<?php
						
						$onevent = strtolower($event->metadata->event);
						
						$example = <<<EOL
<?php

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

?>
EOL;
						highlight_string($example);
						?>
					</div>
				</article>
			<?php endforeach ?>
		</section>
	</body>
</html>