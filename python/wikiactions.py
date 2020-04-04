import mysql.connector
from mysql.connector import Error
import credentials

import mwclient
import login

masterwiki =  mwclient.Site('en.wikipedia.org')
masterwiki.login(login.username,login.password)

def callAPI(params):
    return masterwiki.api(**params)

def calldb(command):
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
        return record
def sendemails():
    results = calldb("select * from wikitasks where task = 'verifyaccount';")
    for result in results:
        user = result[2]
        userresults = calldb("select * from users where id = '"+str(user)+"';")
        for userresult in userresults:
            username = userresult[2]
            params = {'action': 'query',
            'format': 'json',
            'meta': 'tokens'
            }
            raw = callAPI(params)
            code = raw["query"]["token"]["csrftoken"]
            print code
sendemails()