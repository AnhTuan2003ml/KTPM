import smtplib
import sys

sender = "Private Person <from@example.com>"
receiver_email = sys.argv[1]
print(receiver_email)
receiver = f"{receiver_email}"
temp_pwd = sys.argv[2]
token = sys.argv[3]
message = f"""\
Subject: Hi Mailtrap
To: {receiver}
From: {sender}

Dear user,

    Your temporary password is {temp_pwd}. Use this link to reset your password: http://localhost:3000/backend/doc/his_doc_reset_pwd.php?token={token}

    Best regards,
    Hospital Management System"""

with smtplib.SMTP("sandbox.smtp.mailtrap.io", 2525) as server:
    server.starttls()
    server.login("6d68e180a3e482", "7c9be8d3b22889")
    server.sendmail(sender, receiver, message)