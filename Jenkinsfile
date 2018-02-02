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
                    sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.ci.yml up -d"
                    sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.ci.yml exec cli ./smoke_tests_cli.sh"
                    sh "IMAGE_TAG=${commit} docker-compose -f docker-compose.ci.yml exec fpm ./smoke_tests_fpm.sh"
                } finally {
                    // TODO: use down instead of this pair?
                    sh 'docker-compose -f docker-compose.ci.yml stop'
                    sh 'docker-compose -f docker-compose.ci.yml rm -v -f'
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
