<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fa fa-exclamation-triangle"></i> Team Fortress 2 - Super Spray Handler</h2>
		<?php if (!isset($_POST['action']) || $_POST['action'] !== 'edit'): ?>
		<form action="/tf2/super-spray-handler" method="POST">
			<input type="hidden" name="action" value="create">
			<label for="player_nick"><i class="fas fa-user"></i> Player Nick:</label>
			<input type="text" id="player_nick" name="player_nick" required>
			<label for="player_steam_id"><i class="fas fa-id-card"></i> Player Steam ID:</label>
			<input type="text" id="player_steam_id" name="player_steam_id" required>
			<input type="submit" value="Add Spray" class="button system">
		</form>
		<br>
		<hr>
		<form method="GET" action="/tf2/super-spray-handler">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by SteamID" value="<?php echo htmlspecialchars($search); ?>">
			<input type="submit" value="Search">
		</form>
		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th><i class="fas fa-user"></i> Player Nick</th>
						<th><i class="fas fa-id-card"></i> Steam ID</th>
						<th><i class="fas fa-edit"></i> Edit</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($sprays as $spray): ?>
					<tr>
						<td><?php echo $spray['name']; ?></td>
						<td><a href="https://rep.tf/<?php echo $spray['auth']; ?>" target="_blank" class="white-text"><?php echo $spray['auth']; ?></a></td>

						<td>
							<form action="/tf2/super-spray-handler" method="POST">
								<input type="hidden" name="spray_id" value="<?php echo $spray['id']; ?>">
								<input type="hidden" name="action" value="edit">
								<input type="submit" value="Edit" class="button edit">
							</form>
						</td>
						<td>
							<form action="/tf2/super-spray-handler" method="POST">
								<input type="hidden" name="spray_id" value="<?php echo $spray['id']; ?>">
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
		<form id="warningForm" action="/tf2/super-spray-handler" method="POST">
			<input type="hidden" name="spray_id" value="<?php echo $spray['id']; ?>">
			<input type="hidden" name="action" value="update">
			<label for="player_nick"><i class="fas fa-user"></i> Player Nick:</label>
			<input type="text" id="player_nick" name="player_nick" value="<?php echo $spray['name']; ?>" required>
			<label for="player_steam_id"><i class="fas fa-id-card"></i> Player SteamID:</label>
			<input type="text" id="player_steam_id" name="player_steam_id" value="<?php echo $spray['auth']; ?>" required>
			<input type="submit" value="Save" class="button" disabled>
		</form>
		<?php endif; ?>
	</div>
</body>
</html>
