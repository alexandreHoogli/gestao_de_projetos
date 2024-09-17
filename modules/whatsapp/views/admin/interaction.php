<title>WhatsApp Cloud API Business Chat</title>
<?php init_head();
// Store CSRF token in session
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>
<div class="content" id="wrapper">
  <div id="app" class="interaction-container">
    <div class="flex flex-col md:flex-row h-screen">
      <!-- Sidebar -->
      <div class="w-full md:w-1/3 lg:w-1/4 xl:w-1/5 bg-gray-50 border-r border-gray-50 overflow-y-auto">
        <div class="p-4 sticky">
          <input type="text" class="border border-gray-300 rounded-lg px-4 py-2 w-full" placeholder="Search">
        </div>
        <div class="interaction-list">
          <div v-for="(interaction, index) in interactions" :key="interaction.receiver_id" @click="selectinteraction(index)" :class="{ 'bg-gray-100': index === selectedinteractionIndex }" class="interaction-item cursor-pointer bg-white border-b border-gray-50 p-4 flex items-center">
            <div class="mr-3">
              <div class="w-10 h-10 flex items-center justify-center rounded-full" style="background-color:rgb(0 218 95);">
                <p class="text-white font-bold text-base">{{ getAvatarInitials(interaction.name) }}</p>
              </div>
            </div>
            <div>
              <h5 class="mt-0 text-base font-semibold" style="word-wrap: break-word;">{{ interaction.name }}</h5> 
              <span class="underline">{{ interaction.type }}</span>
              <p class="mb-0 text-gray-400 last-message">{{ interaction.last_message }}</p>
            </div>
          </div>
        </div>
      </div>
      <!-- Main interaction area -->
      <div class="w-full md:w-2/3 lg:w-3/4 xl:w-4/5 bg-gray-50 border-r border-gray-50 overflow-y-auto interaction-messages relative">
        <div class="p-4 border-b border-gray-50 bg-gray w-100 bg-white sticky top-0 z-10" v-if="selectedinteraction !== null && typeof selectedinteraction === 'object'">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
                 <div class="w-10 h-10 flex items-center justify-center rounded-full mr-4" style="background-color:rgb(0 218 95);">
                <p class="text-white font-bold text-base">{{ getAvatarInitials(selectedinteraction.name) }}</p>
              </div>
              <div>
                <h5 class="mt-0 text-lg font-semibold">{{ selectedinteraction.name }}</h5>
                <p class="mb-0 text-sm text-gray-600">Phone: {{ selectedinteraction.receiver_id }}</p>
              </div>
            </div>
          </div>
        </div>
<!-- Interaction messages -->
<div class="overflow-y-auto max-h-[95vh] p-4 bg-gray" v-if="selectedinteraction && selectedinteraction.messages">
  <template v-for="(message, index) in selectedinteraction.messages" :key="index">
    <div :class="[message.sender_id === currentUserId ? 'flex justify-end mb-4' : 'flex mb-4', message.size, message]">
      <div :class="{'bg-green-100': message.sender_id === currentUserId, 'bg-blue-50': message.sender_id !== currentUserId}" class="rounded-lg p-3">
        <template v-if="message.type === 'text'">
          <p class="text-sm">{{ message.message }}</p>
        </template>
        <template v-else-if="message.type === 'image'">
          <img :src="message.asset_url" alt="Image" class="max-w-[200px] h-auto rounded-lg">
          <p class="text-sm mt-2" v-if="message.caption">{{ message.caption }}</p>
        </template>
        <template v-else-if="message.type === 'video'">
          <video :src="message.asset_url" controls class="max-w-[200px] h-auto rounded-lg"></video>
          <p class="text-sm mt-2" v-if="message.message">{{ message.message }}</p>
        </template>
        <template v-else-if="message.type === 'document'">
          <a :href="message.asset_url" target="_blank" class="text-blue-500">Download Document</a>
        </template>
        <template v-else-if="message.type === 'audio'">
          <audio :src="message.asset_url" controls class="max-w-[200px] h-auto"></audio>
          <p class="text-sm mt-2" v-if="message.message">{{ message.message }}</p>
        </template>
        <div class="flex items-center mt-2">
          <span class="text-xs text-gray-500">{{ message.time_sent }}</span>
          <span v-if="message.staff_id !== null" class="ml-auto text-xs text-gray-500">ㅤ{{ message.staff_name }}</span>
          <span class="ml-2" v-if="message.sender_id === currentUserId">
            <i v-if="message.status === 'sent'" class="fas fa-check text-gray-500" title="Sent"></i>
            <i v-else-if="message.status === 'delivered'" class="fas fa-check-double text-gray-500" title="Delivered"></i>
            <i v-else-if="message.status === 'read'" class="fas fa-check-double text-blue-500" title="Read"></i>
            <i v-else-if="message.status === 'failed'" class="fas fa-exclamation-circle text-red-500" title="Failed"></i>
            <i v-else-if="message.status === 'deleted'" class="fas fa-trash-circle text-red-500" title="deleted"></i>
          </span>
        </div>
      </div>
    </div>
  </template>
  <br><br><br> <!-- Using <br> for line breaks instead of </br> -->
</div>


        <!-- Message input -->
<div class="p-4 fixed bottom-0 w-3/4 bg-white">
  <form @submit.prevent="sendMessage" class="flex items-center justify-between w-auto">
    <!-- Message input -->
    <input v-model="newMessage" type="text" class="border border-gray-300 rounded-lg px-4 py-2 w-3/4 md:w-full mr-2" placeholder="Type a message..." aria-label="Type a message...">
    <!-- Attachment inputs -->
    <div class="flex items-center w-2/6">
      <label for="imageAttachmentInput" class="mr-2 cursor-pointer">
        <span class="fas fa-image rounded-full p-2"></span>
      </label>
      <input type="file" id="imageAttachmentInput" ref="imageAttachmentInput" @change="handleImageAttachmentChange" class="hidden">

      <label for="videoAttachmentInput" class="mr-2 cursor-pointer">
        <span class="fas fa-video rounded-full p-2"></span>
      </label>
      <input type="file" id="videoAttachmentInput" ref="videoAttachmentInput" @change="handleVideoAttachmentChange" class="hidden">

      <label for="documentAttachmentInput" class="mr-2 cursor-pointer">
        <span class="fas fa-file rounded-full p-2"></span>
      </label>
      <input type="file" id="documentAttachmentInput" ref="documentAttachmentInput" @change="handleDocumentAttachmentChange" class="hidden">

      <!-- Microphone button for audio recording -->
      <div class="attachment action-button">
        <button @click="toggleRecording" type="button" class="microphone-button">
          <span v-if="!recording" class="fas fa-microphone rounded-full p-2"></span>
          <span v-else class="fas fa-stop rounded-full p-2"></span>
        </button>
      </div>
      <!-- Send button -->
      <div class="attachment action-button ml-2 cursor-pointer">
        <button v-if="showSendButton || audioBlob" type="submit" class="send-button">
          <span class="fas fa-paper-plane rounded-full bg-green-400 p-2"></span>
        </button>
      </div>
    </div>
  </form>
</div>

      </div>
    </div>
  </div>
</div>

<?php init_tail(); ?>

<script src="https://cdn.jsdelivr.net/npm/vue@2"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/recorderjs/0.1.0/recorder.min.js" integrity="sha512-Dc8aBUPSsnAiEtyqTYZrldxDfs2FnS8cU7BVHIJ1m5atjKrtQCoPRIn3gsVbKm2qY8NwjpTVTnawoC4XBvEZiQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
  new Vue({
    el: '#app',
    data() {
      return {
        interactions: [],
        currentUserId: '<?php echo get_option('phone_number'); ?>',
        selectedinteractionIndex: null,
        selectedinteraction: null,
        newMessage: '',
        imageAttachment: null,
        videoAttachment: null,
        documentAttachment: null,
        csrfToken: '<?php echo $csrfToken; ?>',
        recording: false,
        audioBlob: null,
      };
    },

    methods: {
      selectinteraction(index) {
        this.selectedinteractionIndex = index;
        this.selectedinteraction = this.interactions[index];
        this.scrollToBottom();
      },
async sendMessage() {
  if (!this.selectedinteraction) return;

  const formData = new FormData();
  formData.append('to', this.selectedinteraction.receiver_id);
  formData.append('csrf_token_name', this.csrfToken);

  // Add message if it exists
  if (this.newMessage.trim()) {
    formData.append('message', this.newMessage);
  }

  // Handle image attachment
  if (this.imageAttachment) {
    formData.append('image', this.imageAttachment);
  }

  // Handle video attachment
  if (this.videoAttachment) {
    formData.append('video', this.videoAttachment);
  }

  // Handle document attachment
  if (this.documentAttachment) {
    formData.append('document', this.documentAttachment);
  }

  // Handle audio attachment
  if (this.audioBlob) {
    formData.append('audio', this.audioBlob);
  }

  try {
    const response = await axios.post('<?php echo admin_url('whatsapp/webhook/send_message') ?>', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });

    if (response.data.success) {
      console.log('Message sent successfully');
      // Clear message input
      this.newMessage = '';
      // Clear attachments
      this.imageAttachment = null;
      this.videoAttachment = null;
      this.documentAttachment = null;
      this.audioBlob = null;
    } else {
      console.error('Failed to send message.');
    }
  } catch (error) {
    console.error('Error:', error);
  }
},
      clearMessage() {
        this.newMessage = '';
        this.attachment = null;
        this.audioBlob = null;
      },
      toggleAttachmentInput() {
        this.$refs.attachmentInput.click();
      },
      handleAttachmentChange(event) {
        const files = event.target.files;
        this.attachment = files[0];
      },
      async fetchinteractions() {
        try {
          const response = await fetch('<?php echo admin_url('whatsapp/interactions') ?>');
          const data = await response.json();
          const previousSelectedinteractionId = this.selectedinteraction ? this.selectedinteraction.receiver_id : null;
          this.interactions = data.interactions || [];

          if (previousSelectedinteractionId) {
            const index = this.interactions.findIndex(interaction => interaction.receiver_id === previousSelectedinteractionId);
            if (index !== -1) {
              this.selectinteraction(index);
            } else {
              this.selectedinteractionIndex = null;
              this.selectedinteraction = null;
            }
          }

        } catch (error) {
          console.error('Error fetching interactions:', error);
        }
      },
scrollToBottom() {
  this.$nextTick(() => {
    const $interactionMessages = $('.interaction-messages');
    console.log($interactionMessages); // Log the element to check if it exists
    if ($interactionMessages.length > 0) {
      console.log($interactionMessages[0].scrollHeight, $interactionMessages[0].clientHeight); // Log scroll properties
      $interactionMessages.scrollTop($interactionMessages[0].scrollHeight - $interactionMessages[0].clientHeight);
    }
  });
},
      getAvatarInitials(name) {
        return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase();
      },
      countUnreadMessages(interactionIndex) {
        const interaction = this.interactions[interactionIndex];
        return interaction.messages.reduce((count, message) => {
          return message.status === 'sent' ? count + 1 : count;
        }, 0);
      },
      async toggleRecording() {
        if (!this.recording) {
          try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.recorder = new MediaRecorder(stream);
            let chunks = [];
            this.recorder.ondataavailable = e => chunks.push(e.data);
            this.recorder.onstop = () => {
              const blob = new Blob(chunks, { type: 'audio/wav' });
              this.audioBlob = blob;
              this.sendMessage();
            };
            this.recorder.start();
            this.recording = true;
          } catch (error) {
            console.error('Error accessing microphone:', error);
          }
        } else {
          this.recorder.stop();
          this.recording = false;
        }
       

      },
       handleImageAttachmentChange(event) {
      this.imageAttachment = event.target.files[0];
    },
    handleVideoAttachmentChange(event) {
      this.videoAttachment = event.target.files[0];
    },
    handleDocumentAttachmentChange(event) {
      this.documentAttachment = event.target.files[0];
    },
    formatTime(timestamp) {
    const date = new Date(timestamp);
    const hour = date.getHours();
    const minute = date.getMinutes();
    return `${hour}:${minute < 10 ? '0' + minute : minute}`;
  }

    },
    created() {
      this.fetchinteractions();
      setInterval(() => {
        this.fetchinteractions();
      }, 5000);
    },
    computed: {
      selectedinteraction() {
        return this.selectedinteractionIndex !== null ? this.interactions[this.selectedinteractionIndex] : null;
      },
      showSendButton() {
      return this.imageAttachment || this.videoAttachment || this.documentAttachment || this.newMessage.trim();
    },

    },
watch: {
    'selectedinteraction.messages': {
    handler() {
      // Using $nextTick to ensure DOM updates are complete before scrolling
      this.$nextTick(() => {
        this.scrollToBottom();
      });
    },
  }
 }
  });
</script>
