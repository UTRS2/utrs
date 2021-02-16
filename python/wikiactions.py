#!/usr/bin/env python
# -*- coding: utf-8 -*-
import mysql.connector
from mysql.connector import Error
import credentials
from datetime import datetime,timedelta,date

import login

def calldb(command,style):
    try:
        print command
        connection = mysql.connector.connect(host=credentials.ip,
                                             database=credentials.database,
                                             user=credentials.user,
                                             password=credentials.password)
        if connection.is_connected():
            cursor = connection.cursor()
            cursor.execute(command)
            if style == "read":
                record = cursor.fetchall()
            if style == "write":
                connection.commit()
    except Error as e:
        print("Error while connecting to MySQL", e)
    finally:
        if (connection.is_connected()):
            cursor.close()
            connection.close()
        if style == "read":return record
        else:return "Done"

def datesince(orig,length):
    today = datetime.now()
    diff = today - timedelta(days=length)
    return diff > orig[0]

def closeNotFound():
    results = calldb("select id from appeals where status = 'NOTFOUND';","read")
    for result in results:
        id = result[0]
        logs = calldb("select timestamp from log_entries where model_id = "+str(id)+" and action = 'create' and model_type = 'App\\\\Models\\\\Appeal';","read")
        if datesince(logs[0], 2):
            calldb("update appeals set status = 'EXPIRE' where id = "+str(id)+";","write")
            calldb("insert into log_entries (user_id, model_id,model_type, action, ip, ua, protected) VALUES ('"+str(0)+"','"+str(id)+"','App\\\\Models\\\\Appeal','closed - expired','DB entry','DB/Python',0);","write")

closeNotFound()
