elifePipeline {
    def commit
    DockerImage cli
    DockerImage fpm
    stage 'Checkout', {
        checkout scm
        commit = elifeGitRevision()
    }

    node('containers-jenkins-plugin') {
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
                cli = DockerImage.elifesciences(this, "annotations_cli", commit).push()
                fpm = DockerImage.elifesciences(this, "annotations_fpm", commit).push()
            }
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
            node('containers-jenkins-plugin') {
                cli.pull().tag('approved').push()
                fpm.pull().tag('approved').push()
            }
        }
    }
}
