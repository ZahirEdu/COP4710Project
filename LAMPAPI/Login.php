<?php
    $inData = getRequestInfo();
	
	$id = 0;
	$name = "";
	$email = "";
    $role = "";
    $universityID = "";

	$conn = new mysqli("localhost", "Zahir", "k9m2q5i0", "UniversityEventManagement");
    if( $conn->connect_error )
	{
		returnWithError( $conn->connect_error );
	}
    else
	{
		$stmt = $conn->prepare("SELECT UID,name,email,role,universityID FROM users WHERE email =? AND password =?");
		$stmt->bind_param("ss", $inData["email"], $inData["password"]);
		$stmt->execute();
		$result = $stmt->get_result();

		if( $row = $result->fetch_assoc()  )
		{
			returnWithInfo($row['ID'], $row['name'], $row['email'], $row['role'], $row['universityID'] );
		}
		else
		{
			returnWithError("No Records Found");
		}

		$stmt->close();
		$conn->close();
	}
?>