<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-user-cog"></i> Team Fortress 2 - Edit Privileges</h2>
		<?php if (!isset($_POST['action']) || $_POST['action'] !== 'edit'): ?>
		<form method="GET" action="/tf2/edit-privileges">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by Nick/SteamID/Flags" value="<?php echo htmlspecialchars($search); ?>">
			<input type="submit" value="Search">
		</form>
		<?php echo $error ?? ''; ?>

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
						<th><i class="fas fa-edit"></i> Edit</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($privileges as $privilege): ?>
					<tr>
						<td>
						<?php if ($privilege['flags'] === 'abcdefgjk' || $privilege['flags'] === 'abcdefgjkz'): ?>
							<a href="admin-logs?search=<?php echo $privilege['identity']; ?>" target="_blank" class="white-text">
								<?php echo $privilege['name']; ?>
							</a>
						<?php else: ?>
							<?php echo $privilege['name']; ?>
						<?php endif; ?>
						</td>
						<td><a href="https://rep.tf/<?php echo $privilege['identity']; ?>" target="_blank" class="white-text"><?php echo $privilege['identity']; ?></a></td>
						<td><?php echo $privilege['flags']; ?></td>
						<td><?php echo $privilege['immunity']; ?></td>
						<td><?php echo date('Y-m-d', $privilege['expire_time']); ?></td>
						<td><?php echo $privilege['discord']; ?></td>
						<td><?php echo $privilege['comment']; ?></td>
						<td>
							<form action="https://admin-panel.ugc-gaming.net/player-analytics/index.php#/stats/players/info/<?php echo $privilege['identity']; ?>" method="GET" target="_blank">
								<button type="submit" class="button edit">
									Analytics
								</button>
							</form>
						</td>
						<td>
							<form action="/tf2/edit-privileges" method="POST">
								<input type="hidden" name="privilege_id" value="<?php echo $privilege['id']; ?>">
								<input type="hidden" name="action" value="edit">
								<input type="submit" value="Edit" class="button edit">
							</form>
						</td>
						<td>
							<form id="deleteForm" action="/tf2/edit-privileges" method="POST">
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
			<?php echo $paginationHTML; ?>
		</div>
		<?php else: ?>
		<form id="warningForm" action="/tf2/edit-privileges" method="POST">
			<input type="hidden" name="privilege_id" value="<?php echo $privilege['id']; ?>">
			<input type="hidden" name="action" value="update">
			<label for="player_nick"><i class="fas fa-user"></i> Player Nick:</label>
			<input type="text" id="player_nick" name="player_nick" value="<?php echo $privilege['name']; ?>" maxlength="32" required>
			<label for="player_steam_id"><i class="fas fa-id-card"></i> Player SteamID:</label>
			<input type="text" id="player_steam_id" name="player_steam_id" value="<?php echo $privilege['identity']; ?>" maxlength="32"  required>
			<label for="flags"><i class="fas fa-key"></i> Privileges:</label>
			<input type="text" id="flags" name="flags" value="<?php echo $privilege['flags']; ?>" maxlength="32" required>
			<label for="immunity"><i class="fas fa-shield-alt"></i> Immunity:</label>
			<input type="text" id="immunity" name="immunity" maxlength="2" value="<?php echo $privilege['immunity']; ?>" required>
			<label for="expired"><i class="fas fa-calendar-alt"></i> Expire:</label>
			<input type="text" id="expire_time" name="expire_time" value="<?php echo $privilege['expire_time']; ?>" required>
			<label for="discord"><i class="fab fa-discord"></i> Discord:</label>
			<input type="text" id="discord" name="discord" maxlength="50" value="<?php echo $privilege['discord']; ?>">
			<label for="comment"><i class="far fa-comment-dots"></i> Comment:</label>
			<input type="text" id="comment" name="comment" maxlength="50" value="<?php echo $privilege['comment']; ?>">
			<input type="submit" value="Save" class="button" disabled>
		</form>
		<?php endif; ?>
	</div>
</body>
</html>
