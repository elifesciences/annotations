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
                sh 'chmod 777 build/ && docker-compose -f docker-compose.ci.yml run ci ./project_tests.sh'
                step([$class: "JUnitResultArchiver", testResults: 'build/phpunit.xml'])
                sh 'docker-compose -f docker-compose.ci.yml run ci ./smoke_tests.sh web'
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

