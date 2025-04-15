<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fa fa-exclamation-triangle"></i> Team Fortress 2 - Manage Warnings</h2>
		<?php if (!isset($_POST['action']) || $_POST['action'] !== 'edit'): ?>
		<form action="/tf2/manage-warnings" method="POST">
			<input type="hidden" name="action" value="create">
			<label for="player_nick"><i class="fas fa-user"></i> Player Nick:</label>
			<input type="text" id="player_nick" name="player_nick" required>
			<label for="player_steam_id"><i class="fas fa-id-card"></i> Player Steam ID:</label>
			<input type="text" id="player_steam_id" name="player_steam_id" required>
			<label for="admin_nick"><i class="fas fa-user-secret"></i> Admin Nick:</label>
			<input type="text" id="admin_nick" name="admin_nick" required>
			<label for="admin_steam_id"><i class="fas fa-id-badge"></i> Admin Steam ID:</label>
			<input type="text" id="admin_steam_id" name="admin_steam_id" required>
			<label for="reason"><i class="fas fa-comment-dots"></i> Reason:</label>
			<input type="text" id="reason" name="reason" required>
			<input type="submit" value="Add Warning" class="button system">
		</form>
		<br>
		<hr>
		<form method="GET" action="/tf2/manage-warnings">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by SteamID" value="<?php echo htmlspecialchars($search); ?>">
			<input type="submit" value="Search">
		</form>
		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th><i class="fas fa-user"></i> Player Nick</th>
						<th><i class="fas fa-id-card"></i> SteamID</th>
						<th><i class="fas fa-user-secret"></i> Admin Nick</th>
						<th><i class="fas fa-globe"></i> Hostname</th>
						<th><i class="fas fa-comment-dots"></i> Reason</th>
						<th><i class="fas fa-clock"></i> Time</th>
						<th><i class="fas fa-calendar-times"></i> Expired</th>
						<th><i class="fas fa-edit"></i> Edit</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($warnings as $warning): ?>
					<tr>
						<td><?php echo $warning['target']; ?></td>
						<td><a href="https://rep.tf/<?php echo $warning['tsteamid']; ?>" target="_blank" class="white-text"><?php echo $warning['tsteamid']; ?></a></td>
						<td><?php echo $warning['admin']; ?></td>
						<td><?php echo str_replace(["\u{2588}", "\u{1}", "UGC.TF |"], '', $warning['hostname']); ?></td>
						<td><?php echo $warning['reason']; ?></td>
						<td><?php echo date('Y-m-d H:i:s', $warning['time']); ?></td>
						<td><?php echo $warning['expired'] ? 'Yes' : 'No'; ?></td>
						<td>
							<form action="/tf2/manage-warnings" method="POST">
								<input type="hidden" name="warning_id" value="<?php echo $warning['id']; ?>">
								<input type="hidden" name="action" value="edit">
								<input type="submit" value="Edit" class="button edit">
							</form>
						</td>
						<td>
							<form action="/tf2/manage-warnings" method="POST">
								<input type="hidden" name="warning_id" value="<?php echo $warning['id']; ?>">
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
		<form id="warningForm" action="/tf2/manage-warnings" method="POST">
			<input type="hidden" name="warning_id" value="<?php echo $warning['id']; ?>">
			<input type="hidden" name="action" value="update">
			<label for="player_nick"><i class="fas fa-user"></i> Player Nick:</label>
			<input type="text" id="player_nick" name="player_nick" value="<?php echo $warning['target']; ?>" required>
			<label for="player_steam_id"><i class="fas fa-id-card"></i> Player SteamID:</label>
			<input type="text" id="player_steam_id" name="player_steam_id" value="<?php echo $warning['tsteamid']; ?>" required>
			<label for="admin_nick"><i class="fas fa-user-secret"></i> Admin Nick:</label>
			<input type="text" id="admin_nick" name="admin_nick" value="<?php echo $warning['admin']; ?>" required>
			<label for="reason"><i class="fas fa-comment-dots"></i> Reason:</label>
			<input type="text" id="reason" name="reason" value="<?php echo $warning['reason']; ?>" required>
			<label for="expired"><i class="fas fa-calendar-times"></i> Expired:</label>
			<select id="expired" name="expired">
				<option value="0" <?php echo $warning['expired'] == 0 ? 'selected' : ''; ?>>No</option>
				<option value="1" <?php echo $warning['expired'] == 1 ? 'selected' : ''; ?>>Yes</option>
			</select>
			<input type="submit" value="Save" class="button" disabled>
		</form>
		<?php endif; ?>
	</div>
</body>
</html>
