<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-clipboard-list"></i> Team Fortress 2 - Vote Logs</h2>
		<form method="GET" action="/tf2/vote-logs">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by SteamID" value="<?php echo htmlspecialchars($search); ?>">
			<input type="submit" value="Search">
		</form>
		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th><i class="far fa-calendar-alt"></i> Date</th>
						<th><i class="fas fa-user"></i> Player Nick</th>
						<th><i class="fas fa-id-card"></i> Steam ID</th>
						<th><i class="fas fa-laptop"></i> IP Address</th>
						<th><i class="fas fa-comment-dots"></i> Log</th>
						<th><i class="fas fa-comment-dots"></i> Reason</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($logs as $log): ?>
					<tr>
						<td><?php echo $log['time']; ?></td>
						<td><?php echo $log['name']; ?></td>
						<td><a href="https://rep.tf/<?php echo $log['steamid']; ?>" target="_blank" class="white-text"><?php echo $log['steamid']; ?></a></td>
						<td><?php echo $log['ip']; ?></td>
						<td><?php echo $log['voteTitle']; ?></td>
						<td><?php echo $log['reason']; ?></td>
						<td>
							<form action="/tf2/vote-logs" method="POST">
								<input type="hidden" name="log_id" value="<?php echo $log['id']; ?>">
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
	</div>
</body>
</html>
