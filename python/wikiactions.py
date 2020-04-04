import mysql.connector
from mysql.connector import Error
import credentials

import localconfig
import mwclient
import login

masterwiki =  mwclient.Site('en.wikipedia.org')
masterwiki.login(login.username,login.password)

def callAPI(params):
    return masterwiki.api(**params)

def verify(command):
    try:
        connection = mysql.connector.connect(host=credentials.ip,
                                             database=credentials.database,
                                             user=credentials.user,
                                             password=credentials.password)
        if connection.is_connected():
            cursor = connection.cursor()
            cursor.execute(command)
            record = cursor.fetchall()

    except Error as e:
        print("Error while connecting to MySQL", e)
    finally:
        if (connection.is_connected()):
            cursor.close()
            connection.close()
            print("MySQL connection is closed")
        return record
def sendemails():
    results = verify("select * from wikitasks where task = 'verifyaccount';")
    for result in results:
        print result
sendemails()