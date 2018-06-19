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
                dockerComposeBuild commit
            }

            stage 'Project tests', {
                dockerProjectTests 'annotations', commit

                dockerComposeSmokeTests(commit, [
                    'scripts': [
                        'cli': './smoke_tests_cli.sh',
                        'fpm': './smoke_tests_fpm.sh',
                    ],
                ])
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
