<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-ban"></i> Team Fortress 2 - ASN Bans</h2>
		<?php if (!isset($_POST['action']) || $_POST['action'] !== 'edit'): ?>
		<form action="/tf2/asn-bans" method="POST">
			<input type="hidden" name="action" value="create">
			<label for="asn"><i class="fas fa-hashtag"></i> AS Number:</label>
			<input type="text" id="asn" name="asn" required>
			<label for="kick_message"><i class="fas fa-sign-out-alt"></i> Kick Message:</label>
			<input type="text" id="kick_message" name="kick_message" required>
			<label for="comment"><i class="fas fa-comment-dots"></i> Comment:</label>
			<input type="text" id="comment" name="comment" required>
			<input type="submit" value="Add ASN" class="button system">
		</form>
		<?php echo $error ?? ''; ?>
		<br>
		<hr>
		<form method="GET" action="/tf2/asn-bans">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by AS Number" value="<?php echo htmlspecialchars($search); ?>">
			<input type="submit" value="Search">
		</form>
		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th><i class="fas fa-hashtag"></i> AS Number</th>
						<th><i class="fas fa-sign-out-alt"></i> Kick Message</th>
						<th><i class="fas fa-comment-dots"></i> Comment</th>
						<th><i class="fas fa-edit"></i> Edit</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($asns as $asn): ?>
					<tr>
						<td><a href="https://bgp.he.net/AS<?php echo $asn['asn']; ?>" target="_blank" class="white-text"><?php echo $asn['asn']; ?></a></td>
						<td><?php echo $asn['kick_message']; ?></td>
						<td><?php echo $asn['comment']; ?></td>
						<td>
							<form action="/tf2/asn-bans" method="POST">
								<input type="hidden" name="asn_id" value="<?php echo $asn['id']; ?>">
								<input type="hidden" name="action" value="edit">
								<input type="submit" value="Edit" class="button edit">
							</form>
						</td>
						<td>
							<form action="/tf2/asn-bans" method="POST">
								<input type="hidden" name="asn_id" value="<?php echo $asn['id']; ?>">
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
		<form id="warningForm" action="/tf2/asn-bans" method="POST">
			<input type="hidden" name="asn_id" value="<?php echo $asn['id']; ?>">
			<input type="hidden" name="action" value="update">
			<label for="asn"><i class="fas fa-hashtag"></i> AS Number:</label>
			<input type="text" id="asn" name="asn" value="<?php echo $asn['asn']; ?>" required>
			<label for="kick_message"><i class="fas fa-sign-out-alt"></i> Kick Message:</label>
			<input type="text" id="kick_message" name="kick_message" value="<?php echo $asn['kick_message']; ?>" required>
			<label for="kick_message"><i class="fas fa-comment-dots"></i> Comment:</label>
			<input type="text" id="comment" name="comment" value="<?php echo $asn['comment']; ?>" required>
			<input type="submit" value="Save" class="button" disabled>
		</form>
		<?php endif; ?>
	</div>
</body>
</html>
