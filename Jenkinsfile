elifePipeline {
    def commit
    elifeOnNode(
        {
            stage 'Checkout', {
                checkout scm
                commit = elifeGitRevision()
            }

            stage 'Container image', {
                sh './build_images.sh'
                sh 'chmod 777 build && docker run -v $(pwd)/build:/srv/annotations/build annotations_ci ./project_tests.sh'
            }
        },
        'elife-libraries--ci'
    )

    stage 'Project tests', {
        lock('annotations--ci') {
            builderDeployRevision 'annotations--ci', commit
            builderProjectTests 'annotations--ci', '/srv/annotations', ['/srv/annotations/build/phpunit.xml']
        }
    }

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

