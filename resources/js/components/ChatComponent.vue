
<template>
    <div class="row">
        <div class="col-4">
            <div class="card card-default ">
                <div class="card-header">Messages</div>
                <div class="card-body p-0 ">
                    <ul class="list-unstyled" style="height:300px; overflow-y:scroll;" v-chat-scroll>
                        <li class="p-2" v-for="(message, index) in messages" :key="index" >
                            <div  :class="{ current: message.user.id ===current, receiver:  message.user.id !== current}">
                                <div  :class="{ currentUser: message.user.id ===current, receiverUser:  message.user.id !== current}">
                                    <strong >{{ message.user.name }}</strong>
                                </div>
                                <div  :class="{ currentUser: message.user.id ===current, receiverUser:  message.user.id !== current}">
                                    {{ message.message }}
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <input
                    @keydown="sendTypingEvent"
                    @keyup.enter="sendMessage"
                    v-model="newMessage"
                    type="text"
                    name="message"
                    placeholder="Enter your message..."
                    class="form-control">

                <button  class="btn btn-success">Send</button>
            </div>
            <span class="text-muted" v-if="activeUser" >{{ activeUser.name }} is typing...</span>
        </div>


<!--        <div class="col-4">-->
<!--            <div class="card card-default">-->
<!--                <div class="card-header">Active Users</div>-->
<!--                <div class="card-body">-->
<!--                    <ul>-->
<!--                        <li class="activeUser nav-link nav-item py-2"  v-for="(user, index) in users" :key="index">-->
<!--                            {{ user.name }}-->
<!--                        </li>-->
<!--                    </ul>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->

        <div class="col-4">
            <div class="card card-default">
                <div class="card-header">Friends:</div>
                <div class="card-body">
                    <ul>
                        <li class="activeUser nav-link nav-item py-2"  v-for="(friend, index) in friends" :key="index" @click="activeFriend=friend.id">
                            {{ friend.name }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Echo from "laravel-echo";

export default {
    props:['user'],
    data() {
        return {
            messages: [],
            newMessage: '',
            users:[],
            activeUser: false,
            typingTimer: false,
            activeFriend: null,
            allUsers: [],
            current: this.user.id,
            // receiver: this.user.id === 1,
        }
    },

    computed:{
        friends(){
            return this.allUsers.filter((user)=>{
                return user.id != this.user.id;
            })
        },
    },

    watch: {
        activeFriend(val){
            this.fetchMessages();
        },
    },

    created() {
        this.fetchUsers();

        window.Echo.join('chat.'+this.user.id)
            .here(user => {
                this.users = user;
            })
            .joining(user => {
                this.users.push(user);
            })
            .leaving(user => {
                this.users = this.users.splice(this.users.indexOf(user), 1);
            })
            .listen('ChatEvent',(event) => {
                this.messages.push(event.chat);
            })
            .listenForWhisper('typing', user => {
                this.activeUser = user;
                if(this.typingTimer) {
                    clearTimeout(this.typingTimer);
                }
                this.typingTimer = setTimeout(() => {
                    this.activeUser = false;
                }, 1000);
            })
    },
    methods: {
        fetchMessages() {
            axios.get('/api/v1/messages/'+this.activeFriend).then(response => {
                this.messages = response.data;
            })
        },

        sendMessage() {
            this.messages.push({
                user: this.user,
                message: this.newMessage
            });

            axios.post('/api/v1/messages/'+this.activeFriend, {message: this.newMessage});
            this.newMessage = '';
        },

        fetchUsers() {
            axios.get('/users').then(response => {
                this.allUsers = response.data;
            });
        },

        sendTypingEvent() {
            window.Echo.join('chat.'+this.activeFriend)
                .whisper('typing', this.user);
        }
    }
}
</script>

