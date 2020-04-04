import mysql.connector
from mysql.connector import Error
import credentials

def verify():
    try:
        connection = mysql.connector.connect(host=credentials.ip,
                                             database=credentials.database,
                                             user=credentials.user,
                                             password=credentials.password)
        if connection.is_connected():
            cursor = connection.cursor()
            cursor.execute("select * from wikitasks where task = 'accountverify';")
            record = cursor.fetchall()
            print record

    except Error as e:
        print("Error while connecting to MySQL", e)
    finally:
        if (connection.is_connected()):
            cursor.close()
            connection.close()
            print("MySQL connection is closed")
verify()