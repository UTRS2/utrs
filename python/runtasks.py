import mwclient
#get guzzle to make requests
import requests
#import for mysql
import MySQLdb
import config

#Get a mysql connection with database utrs, and get a cursor
def get_cursor():
    conn = MySQLdb.connect(host=config.host, user=config.dbuser, passwd=config.dbpass, db=config.dbname)
    cursor = conn.cursor()
    return cursor

#Get the list of all the tasks that are not yet completed
def get_tasks():
    cursor = get_cursor()
    cursor.execute("SELECT * FROM pythonjob")
    tasks = cursor.fetchall()
    return tasks

#if the task name is send_wiki_email, check the wiki_id of the appeal in the appeals table, and send if from that wiki via the api
def send_wiki_email(task):
    cursor = get_cursor()
    cursor.execute("SELECT * FROM appeals WHERE id = %s", (task[1],))
    row = cursor.fetchone()
    print(row)
    wiki = row[13]
    username = row[1]
    print (wiki)
    if wiki == "enwiki":
        site = mwclient.Site("en.wikipedia.org")
        lang = "en"
    elif wiki == "ptwiki":
        site = mwclient.Site("pt.wikipedia.org")
        lang = "pt-PT"
    elif wiki == "global":
        site = mwclient.Site("meta.wikimedia.org")
        lang = "en"
    #login
    site.login(username=config.wiki_user, password=config.wiki_password)
    #check if the user isEmailable first
    
    langpage = requests.get("https://utrs.test/changelang/%s" % str(lang), verify=False)
    #print(langpage.text)
    #get the contents to send in the email from the output of a get request
    subject = requests.get("https://utrs.test/emailpreview/subject/verifyemail", verify=False).text
    message = requests.get("https://utrs.test/emailpreview/message/verifyemail", verify=False).text
    #send the email using mwclient
    try:
        site.email(username,subject,"This is a test email for UTRS. Please ignore it.")
    except mwclient.errors.NoSpecifiedEmail:
        #mark the task as failed
        cursor.execute("UPDATE pythonjob SET status = 'failed' WHERE id = %s", (task[0],))
        return
    #update the EmailBans table to change the last emaoil sent to the current time
    fulluser = username + "@wiki"
    cursor.execute("UPDATE emails SET lastemail = NOW() WHERE email = %s", (fulluser,))
    #mark the task as completed
    cursor.execute("DELETE FROM pythonjob WHERE id = %s", (task[0],))    

#run the tasks
def run_tasks():
    tasks = get_tasks()
    for task in tasks:
        if task[2] == "send_wiki_email":
            send_wiki_email(task)

if __name__ == "__main__":
    run_tasks()