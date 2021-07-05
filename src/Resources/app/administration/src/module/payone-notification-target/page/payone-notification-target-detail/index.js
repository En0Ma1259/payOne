import template from './payone-notification-target-detail.html.twig';

const { Component, Mixin, Data: { Criteria } } = Shopware;

Component.register('payone-notification-target-detail', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel'
    },

    props: {
        notificationTargetId: {
            type: String,
            required: false,
            default: null
        }
    },

    data() {
        return {
            notificationTarget: null,
            isLoading: false,
            isSaveSuccessful: false
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier)
        };
    },

    computed: {
        notificationTargetIsLoading() {
            return this.isLoading || this.notificationTarget == null;
        },

        notificationTargetRepository() {
            return this.repositoryFactory.create('payone_payment_notification_target');
        }
    },

    watch: {
        notificationTargetId() {
            this.createdComponent();
        }
    },

    created() {
        this.createdComponent();
    },

    methods: {
        updateSelection(value) {
            this.notificationTarget.txactions = value;
        },

        createdComponent() {
            if (this.notificationTargetId) {
                this.loadEntityData();
                return;
            }

            Shopware.State.commit('context/resetLanguageToDefault');
            this.notificationTarget = this.notificationTargetRepository.create(Shopware.Context.api);
        },

        loadEntityData() {
            this.isLoading = true;

            this.notificationTargetRepository.get(this.notificationTargetId, Shopware.Context.api).then((notificationTarget) => {
                this.isLoading = false;

                this.notificationTarget = notificationTarget;

                if(null === notificationTarget.txactions) {
                    return;
                }

                if(!notificationTarget.txactions.length) {
                    this.notificationTarget.txactions = null;
                }
            });
        },

        onSave() {
            this.isLoading = true;

            this.notificationTargetRepository.save(this.notificationTarget, Shopware.Context.api).then(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;
                if (this.notificationTargetId === null) {
                    this.$router.push({ name: 'payone.notification.target.detail', params: { id: this.notificationTarget.id } });
                    return;
                }

                this.loadEntityData();
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationSaveErrorMessageRequiredFieldsInvalid'
                    )
                });
                throw exception;
            });
        },

        onCancel() {
            this.$router.push({ name: 'payone.notification.target.list' });
        }
    }
});
