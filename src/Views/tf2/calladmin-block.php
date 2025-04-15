
<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-phone"></i> Team Fortress 2 - CallAdmin Block</h2>
		<?php if (!isset($_POST['action']) || $_POST['action'] !== 'edit'): ?>
		<form action="/tf2/calladmin-block" method="POST">
			<input type="hidden" name="action" value="create">
			<label for="player_steam_id"><i class="fas fa-id-card"></i> SteamID:</label>
			<input type="text" id="player_steam_id" name="player_steam_id" required>
			<label for="time_end"><i class="far fa-clock"></i> Length (minutes):</label>
			<input type="text" id="time_end" name="time_end" required>
			<label for="alias"><i class="fas fa-comment-dots"></i> Reason:</label>
			<input type="text" id="alias" name="alias" required>
			<input type="submit" value="Add Block" class="button system">
		</form>
		<br>
		<hr>
		<form method="GET" action="/tf2/calladmin-block">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by SteamID" value="<?php echo htmlspecialchars($search); ?>">
			<input type="submit" value="Search">
		</form>
		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th><i class="fas fa-id-card"></i> SteamID</th>
						<th><i class="fas fa-stopwatch"></i> Start</th>
						<th><i class="far fa-clock"></i> End</th>
						<th><i class="fas fa-comment-dots"></i> Reason</th>
						<th><i class="fas fa-edit"></i> Edit</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($calls as $call): ?>
					<tr>
						<td><a href="https://rep.tf/<?php echo $call['steam_id']; ?>" target="_blank" class="white-text"><?php echo $call['steam_id']; ?></a></td>
						<td><?php echo date('Y-m-d H:i:s', $call['time_start']); ?></td>
						<td><?php echo date('Y-m-d H:i:s', $call['time_end']); ?></td>
						<td><?php echo $call['alias']; ?></td>
						<td>
							<form action="/tf2/calladmin-block" method="POST">
								<input type="hidden" name="call_id" value="<?php echo $call['id']; ?>">
								<input type="hidden" name="action" value="edit">
								<input type="submit" value="Edit" class="button edit">
							</form>
						</td>
						<td>
							<form action="/tf2/calladmin-block" method="POST">
								<input type="hidden" name="call_id" value="<?php echo $call['id']; ?>">
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
		<form id="warningForm" action="/tf2/calladmin-block" method="POST">
			<input type="hidden" name="call_id" value="<?php echo $call['id']; ?>">
			<input type="hidden" name="action" value="update">
			<label for="player_steam_id"><i class="fas fa-id-card"></i> SteamID:</label>
			<input type="text" id="player_steam_id" name="player_steam_id" value="<?php echo $call['steam_id']; ?>" required>
			<label for="time_end"><i class="fas fa-stopwatch"></i> Remaining Time (minutes):</label>
			<input type="text" id="time_end" name="time_end" value="<?php echo $call['time_end']; ?>" required>
			<label for="alias"><i class="fas fa-comment-dots"></i> Reason:</label>
			<input type="text" id="alias" name="alias" value="<?php echo $call['alias']; ?>" required>
			<input type="submit" value="Save" class="button" disabled>
		</form>
		<?php endif; ?>
	</div>
</body>
</html>
