<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-ban"></i> Team Fortress 2 - Cidr Whitelist</h2>
		<?php if (!isset($_POST['action']) || $_POST['action'] !== 'edit'): ?>
		<form action="/tf2/cidr-whitelist" method="POST">
			<input type="hidden" name="action" value="create">
			<label for="cidr"><i class="fas fa-network-wired"></i> Type:</label>
			<select id="cidr" name="cidr" required>
				<option value="steam">SteamID</option>
				<option value="ip">IP Address</option>
			</select>
			<label for="kick_message"><i class="fas fa-sign-out-alt"></i> SteamID:</label>
			<input type="text" id="kick_message" name="kick_message" required>
			<label for="comment"><i class="fas fa-comment-dots"></i> Comment:</label>
			<input type="text" id="comment" name="comment" required>
			<input type="submit" value="Add SteamID" class="button system">
		</form>
		<br>
		<hr>
		<form method="GET" action="/tf2/cidr-whitelist">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by SteamID/IP" value="<?php echo htmlspecialchars($search); ?>">
			<input type="submit" value="Search">
		</form>
		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th><i class="fas fa-network-wired"></i> Type</th>
						<th><i class="fas fa-sign-out-alt"></i> SteamID</th>
						<th><i class="fas fa-comment-dots"></i> Comment</th>
						<th><i class="fas fa-edit"></i> Edit</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($cidrs as $cidr): ?>
					<tr>
						<td><?php echo $cidr['type']; ?></td>
						<td><?php echo $cidr['identity']; ?></td>
						<td><?php echo $cidr['comment']; ?></td>
						<td>
							<form action="/tf2/cidr-whitelist" method="POST">
								<input type="hidden" name="cidr_id" value="<?php echo $cidr['id']; ?>">
								<input type="hidden" name="action" value="edit">
								<input type="submit" value="Edit" class="button edit">
							</form>
						</td>
						<td>
							<form action="/tf2/cidr-whitelist" method="POST">
								<input type="hidden" name="cidr_id" value="<?php echo $cidr['id']; ?>">
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
		<form id="warningForm" action="/tf2/cidr-whitelist" method="POST">
			<input type="hidden" name="cidr_id" value="<?php echo $cidr['id']; ?>">
			<input type="hidden" name="action" value="update">
			<label for="cidr"><i class="fas fa-network-wired"></i> Type:</label>
			<select id="cidr" name="cidr" required>
				<option value="steam" <?php echo $cidr['type'] == 'steam' ? 'selected' : ''; ?>>SteamID</option>
				<option value="ip" <?php echo $cidr['type'] == 'ip' ? 'selected' : ''; ?>>IP Address</option>
			</select>
			<label for="kick_message"><i class="fas fa-sign-out-alt"></i> SteamID:</label>
			<input type="text" id="kick_message" name="kick_message" value="<?php echo $cidr['identity']; ?>" required>
			<label for="comment"><i class="fas fa-comment-dots"></i> Comment:</label>
			<input type="text" id="comment" name="comment" value="<?php echo $cidr['comment']; ?>" required>
			<input type="submit" value="Save" class="button" disabled>
		</form>
		<?php endif; ?>
	</div>
</body>
</html>
