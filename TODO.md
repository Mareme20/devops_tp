# Jenkinsfile Updates for Slack Notifications and Docker Hub

## Steps to Complete:
1. [x] Add environment variables for Docker Hub and Slack in Jenkinsfile.
2. [x] Insert new 'Push to Docker Hub' stage after 'Build Image'.
3. [x] Add Slack notifications in post { success {} failure {} } blocks.
4. [x] Update post { always {} } if needed for cleanup.
5. [x] Fix image naming consistency and Docker login for push.

**Jenkinsfile updated successfully with Slack notifications and Docker Hub push.**

**Next steps:**
- Update `DOCKERHUB_REPO = 'yourusername/devops-tp'` with your Docker Hub repo.
- Configure Jenkins credentials: ID `dockerhub-creds` (Docker Hub username/password).
- Install Slack Notification plugin in Jenkins, configure token if needed.
- Run the pipeline to test.
