import pywikibot
#get guzzle to make requests
import requests

#Get a mysql connection with database utrs, and get a cursor
def get_cursor():
    conn = MySQLdb.connect(host="localhost", user="root", passwd="root", db="utrs")
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
    cursor.execute("SELECT wiki_id FROM appeals WHERE appeal_id = %s", (task[1],))
    row = cursor.fetchone()
    wiki = row["wiki"]
    user = row["appealfor"]
    if wiki == "enwiki":
        site = pywikibot.Site("en", "wikipedia")
        lang = "en"
    elif wiki == "ptwiki":
        site = pywikibot.Site("pt", "wikipedia")
        lang = "pt-PT"
    elif wiki == "global":
        site = pywikibot.Site("meta", "wikimedia")
        lang = "en"
    #get the user object from the wiki
    user = pywikibot.User(site, user)    
    #check if the user isEmailable first
    if not user.isEmailable():
        #mark the task as failed
        cursor.execute("UPDATE pythonjob SET status = 'failed' WHERE job_id = %s", (task[0],))
        return
    requests.get("http://utrs-beta.wmflabs.org/changelang/" % lang)
    #get the contents to send in the email from the output of a get request
    message = requests.get("http://utrs-beta.wmflabs.org/emailpreview/verifyemail/%s" % task[1]).text
    subject = requests.get("http://utrs-beta.wmflabs.org/emailpreview/verifyemail/subject").text
    #send the email using pywikibot
    user.send_email(subject, message)
    


#send an email to the email address with the message
def send_email(email, message, wiki_id):
    print("Sending email to %s with message %s" % (email, message))
    #using pywikibot to send the email
    site = pywikibot.Site("en", "wikipedia")
    user = pywikibot.User(site, "Example")
    user.sendMail(email, "UTRS", message)


#delete the task from the pythonjob table when done
def mark_task_completed(task):
    cursor = get_cursor()
    cursor.execute("DELETE FROM pythonjob WHERE job_id = %s", (task[0],))

#run the tasks
def run_tasks():
    tasks = get_tasks()
    for task in tasks:
        if task[2] == "send_wiki_email":
            send_wiki_email(task)
        mark_task_completed(task)