<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="/assets/css/style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<title><?php echo $config['site']['name']; ?></title>
</head>
<body>
	<header>
		<h1><i class="<?php echo $config['site']['icon']; ?>"></i> <?php echo $config['site']['name']; ?></h1>
		<nav>
			<div class="menu-icon">
				<i class="fas fa-bars"></i>
			</div>
			<ul>
				<li><a href="/"><i class="fas fa-home"></i> Home</a></li>
				<?php foreach ($config['games'] as $game) : ?>
					<?php if ($game['visible']) : ?>
						<li><a href="#"><i class="<?php echo $game['icon']; ?>"></i> <?php echo $game['name']; ?></a>
							<ul class="dropdown">
								<?php
								$adminPanels = [];
								$smodPanels = [];
								$modPanels = [];
								foreach ($game['panels'] as $panel) {
									if ($panel['access'] === 'admin') {
										$adminPanels[] = $panel;
									} elseif ($panel['access'] === 'smod') {
										$smodPanels[] = $panel;
									} elseif ($panel['access'] === 'mod') {
										$modPanels[] = $panel;
									}
								}
								?>
								<?php if (!empty($adminPanels)) : ?>
									<li>
										<span class="dropdown-title"><i class="fas fa-crown"></i> Administrators</span>
										<?php foreach ($adminPanels as $panel) : ?>
											<a href="<?php echo $panel['route']; ?>"><i class="<?php echo $panel['icon']; ?>"></i> <?php echo $panel['name']; ?></a>
										<?php endforeach; ?>
									</li>
								<?php endif; ?>
								<?php if (!empty($smodPanels)) : ?>
									<li>
										<span class="dropdown-title"><i class="fas fa-chess-king"></i> Senior Moderators</span>
										<?php foreach ($smodPanels as $panel) : ?>
											<a href="<?php echo $panel['route']; ?>"><i class="<?php echo $panel['icon']; ?>"></i> <?php echo $panel['name']; ?></a>
										<?php endforeach; ?>
									</li>
								<?php endif; ?>
								<?php if (!empty($modPanels)) : ?>
									<li>
										<span class="dropdown-title"><i class="fas fa-user-shield"></i> Moderators</span>
										<?php foreach ($modPanels as $panel) : ?>
											<a href="<?php echo $panel['route']; ?>"><i class="<?php echo $panel['icon']; ?>"></i> <?php echo $panel['name']; ?></a>
										<?php endforeach; ?>
									</li>
								<?php endif; ?>
							</ul>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</nav>
	</header>
	<main>
