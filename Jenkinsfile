elifePipeline {
    def commit
    DockerImage cli
    DockerImage fpm
    stage 'Checkout', {
        checkout scm
        commit = elifeGitRevision()
    }

    elifeOnNode(
        {
            stage 'Build images', {
                checkout scm
                sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.ci.yml build"
            }

            stage 'Project tests', {
                try {
                    sh "chmod 777 build/ && IMAGE_TAG=${commit} docker-compose -f docker-compose.ci.yml run --rm ci ./project_tests.sh"
                    step([$class: "JUnitResultArchiver", testResults: 'build/phpunit.xml'])
                    sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.ci.yml run --rm ci ./smoke_tests.sh web"
                } finally {
                    sh 'docker-compose -f docker-compose.ci.yml stop'
                    sh 'docker-compose -f docker-compose.ci.yml rm -v -f'
                }
            }

            elifeMainlineOnly {
                stage 'Push images', {
                    cli = DockerImage.elifesciences("annotations_cli", commit)
                    cli.push()
                    fpm = DockerImage.elifesciences("annotations_fpm", commit)
                    fpm.push()
                }
            }
        },
        'elife-libraries--ci'
    )

    elifeMainlineOnly {
        stage 'End2end tests', {
            elifeSpectrum(
                deploy: [
                    stackname: 'annotations--end2end',
                    revision: commit,
                    folder: '/srv/annotations'
                ],
                marker: 'annotations'
            )
        }

        stage 'Deploy to continuumtest', {
            lock('annotations--continuumtest') {
                builderDeployRevision 'annotations--continuumtest', commit
                builderSmokeTests 'annotations--continuumtest', '/srv/annotations'
            }
        }

        stage 'Approval', {
            elifeGitMoveToBranch commit, 'approved'
            elifeOnNode(
                {
                    cli.tag('approved').push()
                    fpm.tag('approved').push()
                },
                'elife-libraries--ci'
            )
        }
    }
}

public class DockerImage implements Serializable {
    private final def script
    private final String repository
    private final String tag

    public static elifesciences(String project) {
        return new DockerImage("elifesciences/${project}")
    }

    public DockerImage(script, repository, tag) {
        this.script = script
        this.repository = repository
        this.tag = tag
    }

    public void push()
    {
        this.script.sh "docker push ${repository}:${tag}"
    }

    public DockerImage tag(newTag)
    {
        this.script.sh "docker tag ${repository}:${tag} ${repository}:${newTag}"
        return new DockerImage(repository, newTag)
    }
}

