<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-clipboard-list"></i> Team Fortress 2 - ASN/Cidr Logs</h2>
		<form method="GET" action="/tf2/asn-cidr-logs">
			<label for="search"><i class="fas fa-search"></i> Search:</label>
			<input type="text" name="search" placeholder="Search by SteamID/AS Number" value="<?php echo htmlspecialchars($search); ?>">
			<input type="submit" value="Search">
		</form>
		<div class="table-wrapper">
			<table>
				<thead>
					<tr>
						<th><i class="far fa-calendar-alt"></i> Date</th>
						<th><i class="fas fa-user"></i> Player Nick</th>
						<th><i class="fas fa-network-wired"></i> ASN/Cidr</th>
						<th><i class="fas fa-id-card"></i> Steam ID</th>
						<th><i class="fas fa-laptop"></i> IP Address</th>
						<th><i class="fas fa-globe"></i> Hostname</th>
						<th><i class="fas fa-comment-dots"></i> Comment</th>
						<th><i class="fas fa-trash-alt"></i> Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($asnscidrs as $asncidr): ?>
					<tr>
						<td><?php echo $asncidr['time']; ?></td>
						<td><?php echo $asncidr['name']; ?></td>
						<td><a href="https://bgp.he.net/AS<?php echo $asncidr['cidrasn']; ?>" target="_blank" class="white-text"><?php echo $asncidr['cidrasn']; ?></a></td>
						<td><?php echo $asncidr['steamid']; ?></td>
						<td><?php echo $asncidr['ip']; ?></td>
						<td><?php echo str_replace(["\u{2588}", "\u{1}", "UGC.TF |"], '', $asncidr['servername']); ?></td>
						<td><?php echo $asncidr['comment']; ?></td>
						<td>
							<form action="/tf2/asn-cidr-logs" method="POST">
								<input type="hidden" name="asncidr_id" value="<?php echo $asncidr['id']; ?>">
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
