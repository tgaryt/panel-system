<!DOCTYPE html>
<html lang="en">
<body>
	<div class="container">
		<h2><i class="fas fa-plus"></i> Team Fortress 2 - Add High Privileges</h2>
		<form method="POST" action="/tf2/add-high-privileges">
			<label for="steamid"><i class="fas fa-id-card"></i> SteamID:</label>
			<input type="text" name="steamid" id="steamid" placeholder="STEAM_0:0:123456789" required>

			<label for="name"><i class="fas fa-user"></i> Username:</label>
			<input type="text" id="name" name="name" required>

			<label for="privileges"><i class="fas fa-key"></i> Privileges:</label>
			<select id="privileges" name="privileges" required>
				<option value="abcdefgjkz">Administrator</option>
			</select>

			<label for="immunity"><i class="fas fa-shield-alt"></i> Immunity:</label>
			<select id="immunity" name="immunity" required>
				<option value="0">0</option>
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="30">30</option>
				<option value="40">40</option>
				<option value="50">50</option>
			</select>

			<label for="comment"><i class="fas fa-comment-dots"></i> Comment:</label>
			<input type="text" name="comment" id="comment" placeholder="Optional">

			<label for="discord"><i class="fab fa-discord"></i> Discord:</label>
			<input type="text" id="discord" name="discord" placeholder="Optional">

			<label for="days"><i class="fas fa-calendar-alt"></i> Duration:</label>
			<select id="days" name="days" required>
				<option value="7">7 days</option>
				<option value="14">14 days</option>
				<option value="21">21 days</option>
				<option value="30">30 days</option>
				<option value="90">90 days</option>
				<option value="180">180 days</option>
				<option value="365">1 Year</option>
				<option value="lifetime">Lifetime</option>
			</select>

			<input type="submit" value="Add Privileges" class="button system">
		</form>

		<?php echo $error ?? ''; ?>
		<?php echo $success ?? ''; ?>
	</div>
</body>
</html>
