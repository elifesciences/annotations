elifePipeline {
    def commit
    elifeOnNode(
        {
            stage 'Checkout', {
                checkout scm
                commit = elifeGitRevision()
            }

            stage 'Container image', {
                sh 'docker-compose -f docker-compose.ci.yml build'
            }

            stage 'Project tests', {
                try {
                    sh 'chmod 777 build/ && docker-compose -f docker-compose.ci.yml run --rm ci ./project_tests.sh'
                    step([$class: "JUnitResultArchiver", testResults: 'build/phpunit.xml'])
                    sh 'docker-compose -f docker-compose.ci.yml run --rm ci ./smoke_tests.sh web'
                } finally {
                    sh 'docker-compose -f docker-compose.ci.yml stop'
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
        }
    }
}

