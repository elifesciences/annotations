elifePipeline {
    def commit
    stage 'Checkout', {
        checkout scm
        commit = elifeGitRevision()
    }

    elifeOnNode(
        {
            stage 'Build images', {
                checkout scm
                sh 'docker-compose -f docker-compose.ci.yml build'
            }

            stage 'Project tests', {
                try {
                    sh 'chmod 777 build/ && docker-compose -f docker-compose.ci.yml run --rm ci ./project_tests.sh'
                    step([$class: "JUnitResultArchiver", testResults: 'build/phpunit.xml'])
                    sh 'docker-compose -f docker-compose.ci.yml run --rm ci ./smoke_tests.sh web'
                } finally {
                    sh 'docker-compose -f docker-compose.ci.yml stop'
                    sh 'docker-compose -f docker-compose.ci.yml rm -v -f'
                }
            }

            elifeMainlineOnly {
                stage 'Push images', {
                    sh "docker push elifesciences/annotations_cli"
                    sh "docker tag elifesciences/annotations_cli elifesciences/annotations_cli:${commit} && docker push elifesciences/annotations_cli:${commit}"
                    sh "docker push elifesciences/annotations_fpm:latest"
                    sh "docker tag elifesciences/annotations_fpm elifesciences/annotations_fpm:${commit} && docker push elifesciences/annotations_fpm:${commit}"
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
                    sh "docker tag elifesciences/annotations_fpm:${commit} elifesciences/annotations_fpm:approved && docker push elifesciences/annotations_fpm:approved"
                },
                'elife-libraries--ci'
            )
        }
    }
}

