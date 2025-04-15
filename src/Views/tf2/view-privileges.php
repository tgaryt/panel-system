<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com; style-src 'self' https://cdnjs.cloudflare.com; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self'; img-src 'self' data:;">
</head>
<body>
	<div class="container">
		<h2><i class="fas fa-eye"></i> Team Fortress 2 - Manage Privileges</h2>
		<form method="GET" action="/tf2/view-privileges">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by Nick/SteamID/Flags" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
			<input type="submit" value="Search">
		</form>

		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th><i class="fas fa-user"></i> Player Nick</th>
						<th><i class="fas fa-id-card"></i> SteamID</th>
						<th><i class="fas fa-key"></i> Privileges</th>
						<th><i class="fas fa-shield-alt"></i> Immunity</th>
						<th><i class="fas fa-calendar-alt"></i> Expire</th>
						<th><i class="fab fa-discord"></i> Discord</th>
						<th><i class="far fa-comment-dots"></i> Comment</th>
						<th><i class="fas fa-chart-line"></i> Analytics</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($privileges as $privilege): ?>
					<tr>
						<td>
						<?php if ($privilege['flags'] === 'abcdefgjk' || $privilege['flags'] === 'abcdefgjkz'): ?>
							<a href="admin-logs?search=<?php echo htmlspecialchars($privilege['identity'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="white-text">
								<?php echo htmlspecialchars($privilege['name'], ENT_QUOTES, 'UTF-8'); ?>
							</a>
						<?php else: ?>
							<?php echo htmlspecialchars($privilege['name'], ENT_QUOTES, 'UTF-8'); ?>
						<?php endif; ?>
						</td>
						<td><a href="https://rep.tf/<?php echo htmlspecialchars($privilege['identity'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="white-text"><?php echo htmlspecialchars($privilege['identity'], ENT_QUOTES, 'UTF-8'); ?></a></td>
						<td><?php echo htmlspecialchars($privilege['flags'], ENT_QUOTES, 'UTF-8'); ?></td>
						<td><?php echo htmlspecialchars($privilege['immunity'], ENT_QUOTES, 'UTF-8'); ?></td>
						<td>
							<?php 
								$expireDate = date('Y-m-d', (int)$privilege['expire_time']); 
								$currentDate = new DateTime();
								$expirationDate = new DateTime($expireDate);
								$interval = $currentDate->diff($expirationDate);
								$daysLeft = $interval->days + 1;
							?>
							<?php echo $expireDate . ' (' . $daysLeft . ' days)'; ?>
						</td>
						<td><?php echo htmlspecialchars($privilege['discord'], ENT_QUOTES, 'UTF-8'); ?></td>
						<td><?php echo htmlspecialchars($privilege['comment'], ENT_QUOTES, 'UTF-8'); ?></td>
						<td>
							<form action="https://admin-panel.ugc-gaming.net/player-analytics/index.php#/stats/players/info/<?php echo htmlspecialchars($privilege['identity'], ENT_QUOTES, 'UTF-8'); ?>" method="GET" target="_blank">
								<button type="submit" class="button edit">
									Analytics
								</button>
							</form>
						</td>
						<td>
							<form id="deleteForm" action="/tf2/view-privileges" method="POST">
								<input type="hidden" name="privilege_id" value="<?php echo $privilege['id']; ?>">
								<input type="hidden" name="action" value="delete">
								<input type="submit" value="Delete" class="button danger">
							</form>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<div class="pagination">
			<?php echo $paginationHTML ?>
		</div>
	</div>
</body>
</html>
