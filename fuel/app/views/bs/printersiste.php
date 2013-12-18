<table class="table table-striped">
	<thead>
		<tr>
			<th>Tid</th>
			<th>Bruker</th>
			<th>Navn</th>
			<th>Printer</th>
			<th>Antall sider</th>
		</tr>
	</thead>
	<tbody>

<?php
foreach ($prints as $row)
{
	$d = new DateTime($row['jobdate']);
	$user = Auth::get_user_details($row['username']);
	$name = $user && isset($user['realname']) ? htmlspecialchars($user['realname']) : '<i>Ukjent</i>';

	echo '
		<tr>
			<td>'.$d->format("Y-m-d H:i").'</td>
			<td>'.htmlspecialchars($row['username']).'</td>
			<td>'.$name.'</td>
			<td>'.htmlspecialchars($row['printername']).'</td>
			<td style="text-align: right">'.$row['jobsize'].'</td>
		</tr>';
}
?>
	</tbody>
</table>