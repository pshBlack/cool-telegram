export interface User {
  user_id: number;
  username: string;
  first_name: string | null;
  last_name: string | null;
  email: string;
  google_id: number | null;
  avatar_url: string | null;
  bio: string | null;
  created_at: Date;
  last_seen_at: Date;
  updated_at: Date;
}
export interface Message {
  message_id: number;
  chat_id: number;
  sender_id: number;
  content: string;
  sent_at: Date;
  is_read: boolean;
}

export interface Chat {
  chat_id: number;
  chat_type: string;
  chat_name: string | null;
  chat_avatar_url: string | null;
  created_by: number | null;
  created_at: Date;
  updated_at: Date;
  display_name: string | null;
  pivot: {
    user_id: number;
    chat_id: number;
    role: "owner" | "member";
    joined_at: Date;
  };
  users: User[];
  messages: Message[] & {
    content: string;
  };
}
