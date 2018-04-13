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
                sh "IMAGE_TAG=${commit} docker-compose build"
            }

            stage 'Project tests', {
                try {
                    def container = sh(script: "docker run -d elifesciences/annotations_ci:${commit}", returnStdout: true).trim()
                    sh "IMAGE_TAG=${commit} docker-compose run ci ./project_tests.sh"
                    sh "docker cp ${container}:/srv/annotations/build/. build"
                    step([$class: "JUnitResultArchiver", testResults: 'build/phpunit.xml'])
                    sh "IMAGE_TAG=${commit} docker-compose up -d"
                    sh "IMAGE_TAG=${commit} docker-compose exec -T cli ./smoke_tests_cli.sh"
                    sh "IMAGE_TAG=${commit} docker-compose exec -T fpm ./smoke_tests_fpm.sh"
                } finally {
                    sh 'docker-compose down'
                }
            }

            elifeMainlineOnly {
                stage 'Push images', {
                    cli = DockerImage.elifesciences(this, "annotations_cli", commit)
                    cli.push()
                    fpm = DockerImage.elifesciences(this, "annotations_fpm", commit)
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
